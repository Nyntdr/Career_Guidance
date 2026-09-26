<?php

namespace App\Console\Commands;

use App\Models\ResumeSection;
use App\Services\Ml\ModelRepository;
use App\Services\Ml\ResumeLineVectorizer;
use App\Services\ResumeParser;
use App\Support\Settings;
use Illuminate\Console\Command;
use Rubix\ML\Classifiers\RandomForest;
use Rubix\ML\Clusterers\KMeans;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Throwable;

class MlTrain extends Command
{
    protected $signature = 'ml:train
        {--model=all : Which model to train: all, classifier, or clusterer}
        {--include-resumes : Also harvest weak labels from stored resumes}
        {--from-fixtures : Also harvest weak labels by dictionary-segmenting tests/Fixtures/resumes/*}
        {--min-sections=3 : Minimum dictionary-detected sections before a fixture is trusted as a label source}
        {--max-per-class=150 : Cap training samples per label (0 = unlimited), bounding memory and class imbalance. Keep at or below 150: above ~150 per label the forest no longer loads within the 256 MB application limit}';

    protected $description = 'Train the resume-section Random Forest classifier and K-Means clusterer';

    protected const VALID_LABELS = [
        'experience', 'skills', 'education', 'certifications', 'projects',
        'publications', 'awards', 'volunteer', 'languages', 'interests',
        'references', 'summary', 'contact',
    ];

    public function handle(ModelRepository $repository): int
    {
        // Training materializes one dense vector per sample (corpus size x
        // vocabulary features), which overruns the CLI default on a harvested
        // corpus. This is a batch job, so raise the ceiling for its duration.
        $previousMemory = ini_get('memory_limit');
        ini_set('memory_limit', (string) config('ml.train_memory_limit', '1G'));

        try {
            return $this->trainModels($repository);
        } finally {
            ini_set('memory_limit', (string) $previousMemory);
        }
    }

    protected function trainModels(ModelRepository $repository): int
    {
        $model = $this->option('model') ?? 'all';

        // Rubix's RandomForest (bootstrap resampling) and KMeans (k-means++
        // seeding) both draw from PHP's global RNG, so an unseeded run produces
        // a different model every time — holdout accuracy on the shipped
        // corpus swings ~17-26% between runs, and the nightly schedule would
        // silently swap the model in production. Seeding makes a given corpus
        // and configuration reproduce byte-for-byte.
        $this->seedRandom();

        if (! in_array($model, ['all', 'classifier', 'clusterer'], true)) {
            $this->error('--model must be one of: all, classifier, clusterer');

            return self::FAILURE;
        }

        $repository->ensureDirectories();

        [$samples, $labels, $lines] = $this->trainingCorpus($repository);

        if ($samples === []) {
            $this->error('No labeled training lines found. Seed '.$repository->corpusPath().' first.');

            return self::FAILURE;
        }

        $vocab = ResumeLineVectorizer::buildVocabulary($lines);
        $repository->saveVocab($vocab);
        $this->line('Vocabulary: '.count($vocab).' terms');

        $vectorizer = new ResumeLineVectorizer($vocab);
        $vectors = array_map(fn ($line) => $vectorizer->vectorize($line), $samples);

        $meta = [
            'trained_at' => now()->toDateTimeString(),
            'corpus_samples' => count($samples),
            'vocab_size' => count($vocab),
        ];

        if ($model === 'all' || $model === 'classifier') {
            $meta['classifier_accuracy'] = $this->trainClassifier($vectors, $labels);
        }

        if ($model === 'all' || $model === 'clusterer') {
            $this->trainClusterer($vectors);
        }

        $repository->saveMeta($meta);
        $this->info('Training metadata written to '.$repository->metaPath());

        return self::SUCCESS;
    }

    /**
     * Seed PHP's global RNG so a training run is reproducible.
     *
     * Set `ml.train_seed` to null to opt back into unseeded training.
     */
    protected function seedRandom(): void
    {
        $seed = config('ml.train_seed');

        if ($seed === null) {
            $this->line('Random seed not set; training will not be reproducible.');

            return;
        }

        mt_srand((int) $seed);
        srand((int) $seed);

        $this->line('Random seed: '.(int) $seed);
    }

    /**
     * @return array{0: list<string>, 1: list<string>, 2: list<string>}
     */
    protected function trainingCorpus(ModelRepository $repository): array
    {
        $samples = [];
        $labels = [];
        $lines = [];

        foreach ($this->bootstrappedSamples() as $sample) {
            $samples[] = $sample['line'];
            $labels[] = $sample['label'];
            $lines[] = $sample['line'];
        }

        if ($this->option('include-resumes')) {
            $harvested = 0;
            foreach (ResumeSection::query()->whereIn('section_type', self::VALID_LABELS)->get() as $section) {
                foreach (preg_split('/\r\n|\r|\n/', (string) $section->content) ?: [] as $content) {
                    $line = trim($content);
                    if ($line === '' || mb_strlen($line) < 3 || in_array($line, $lines, true)) {
                        continue;
                    }
                    $samples[] = $line;
                    $labels[] = $section->section_type;
                    $lines[] = $line;
                    $harvested++;
                }
            }
            if ($harvested > 0) {
                $this->line('Harvested '.$harvested.' weak labels from stored resumes');
            }
        }

        if ($this->option('from-fixtures')) {
            $harvested = $this->harvestFromFixtures($samples, $labels, $lines);
            $this->line('Harvested '.$harvested.' weak labels from test fixtures');
        }

        [$samples, $labels] = $this->capPerClass($samples, $labels);

        return [$samples, $labels, $lines];
    }

    /**
     * Trim the corpus to at most N samples per label.
     *
     * Harvested fixture data is heavily skewed — long resumes contribute
     * hundreds of 'experience' lines while rare labels contribute a handful —
     * which both skews training and, at ~29k samples, exhausts memory when
     * vectorized. Capping per class keeps the label distribution roughly even.
     *
     * @param  list<string>  $samples
     * @param  list<string>  $labels
     * @return array{0: list<string>, 1: list<string>}
     */
    protected function capPerClass(array $samples, array $labels): array
    {
        $max = (int) $this->option('max-per-class');
        if ($max <= 0) {
            return [$samples, $labels];
        }

        $counts = [];
        $keptSamples = [];
        $keptLabels = [];

        foreach ($samples as $i => $sample) {
            $label = $labels[$i] ?? null;
            if ($label === null) {
                continue;
            }

            $seen = $counts[$label] ?? 0;
            if ($seen >= $max) {
                continue;
            }

            $counts[$label] = $seen + 1;
            $keptSamples[] = $sample;
            $keptLabels[] = $label;
        }

        $dropped = count($samples) - count($keptSamples);
        if ($dropped > 0) {
            $this->line(sprintf(
                'Capped to %d samples per label: kept %d, dropped %d',
                $max,
                count($keptSamples),
                $dropped,
            ));
        }

        ksort($counts);
        $this->line('Label distribution: '.json_encode($counts));

        return [$keptSamples, $keptLabels];
    }

    /**
     * Grow the corpus from the bundled example resumes.
     *
     * The tracked corpus in database/ml is only ~150 lines spread over 13
     * classes, which is far too small for a 500-tree forest — holdout accuracy
     * lands near 35% and every prediction falls below the heading threshold, so
     * the ML path can never fire. The 200+ example resumes carry tens of
     * thousands of real lines, so dictionary-segmenting them (ML forced off, to
     * avoid labelling the model with its own output) yields a corpus large
     * enough to actually learn section shape.
     *
     * Labels are weak: a resume whose dictionary pass finds few sections is
     * skipped, because its section boundaries are exactly what is in doubt.
     *
     * @param  list<string>  $samples
     * @param  list<string>  $labels
     * @param  list<string>  $lines
     * @return int Number of lines added.
     */
    protected function harvestFromFixtures(array &$samples, array &$labels, array &$lines): int
    {
        $dir = base_path('tests/Fixtures/resumes');
        if (! is_dir($dir)) {
            $this->error('Fixture directory not found: '.$dir);

            return 0;
        }

        $minSections = max((int) $this->option('min-sections'), 2);

        // Force the dictionary-only path so the harvest cannot depend on the
        // very model it is about to train.
        $previous = Settings::get('ml_enabled');
        Settings::save(['ml_enabled' => 'disabled']);

        $harvested = 0;
        $trusted = 0;
        $skipped = 0;
        $seen = array_flip($lines);

        try {
            $parser = app(ResumeParser::class);

            foreach (glob($dir.'/*.txt') ?: [] as $path) {
                $text = @file_get_contents($path);
                if ($text === false || trim($text) === '') {
                    continue;
                }

                $sections = collect($parser->segmentSections($text))
                    ->filter(fn ($section) => in_array($section['type'], self::VALID_LABELS, true));

                if ($sections->count() < $minSections) {
                    $skipped++;

                    continue;
                }

                $trusted++;

                foreach ($sections as $section) {
                    foreach (preg_split('/\r\n|\r|\n/', (string) $section['content']) ?: [] as $content) {
                        $line = trim($content);

                        if ($line === '' || mb_strlen($line) < 3) {
                            continue;
                        }

                        $key = mb_strtolower($line);
                        if (isset($seen[$key])) {
                            continue;
                        }

                        $seen[$key] = true;
                        $samples[] = $line;
                        $labels[] = $section['type'];
                        $lines[] = $line;
                        $harvested++;
                    }
                }
            }
        } finally {
            if ($previous === null) {
                Settings::save(['ml_enabled' => 'enabled']);
            } else {
                Settings::save(['ml_enabled' => $previous]);
            }
        }

        $this->line(sprintf(
            'Fixtures: %d trusted, %d skipped (fewer than %d dictionary sections)',
            $trusted,
            $skipped,
            $minSections,
        ));

        return $harvested;
    }

    protected function bootstrappedSamples(): array
    {
        $loaded = $this->readCorpus($this->corpusPath());

        if ($loaded === null) {
            // Fall back to the tracked fixture shipped in database/ml so a
            // fresh checkout can train without a pre-seeded storage volume.
            $shipped = database_path('ml/section_lines.json');
            if (is_file($shipped)) {
                $loaded = $this->readCorpus($shipped);
                if ($loaded !== null && is_dir(dirname($this->corpusPath()))) {
                    copy($shipped, $this->corpusPath());
                }
            }
        }

        return $loaded ?? [];
    }

    protected function corpusPath(): string
    {
        return config('ml.training_path').'/'.config('ml.corpus_file', 'section_lines.json');
    }

    protected function readCorpus(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        try {
            $decoded = json_decode((string) file_get_contents($path), true);
        } catch (Throwable) {
            return null;
        }

        if (! is_array($decoded) || ! isset($decoded['samples']) || ! is_array($decoded['samples'])) {
            return null;
        }

        return array_values(array_filter(array_map(
            fn ($sample) => is_array($sample) && isset($sample['line'], $sample['label'])
                ? ['line' => (string) $sample['line'], 'label' => (string) $sample['label']]
                : null,
            $decoded['samples'],
        )));
    }

    /**
     * @param  list<list<float>>  $vectors
     * @param  list<string>  $labels
     */
    protected function trainClassifier(array $vectors, array $labels): ?float
    {
        $this->line('Training Random Forest ('.count($vectors).' samples)...');

        $holdout = (float) config('ml.holdout', 0.2);
        [$training, $testing] = Labeled::quick($vectors, $labels)->stratifiedSplit(1 - $holdout);

        $forest = new RandomForest(
            null,
            (int) config('ml.estimators', 200),
            (float) config('ml.ratio', 0.3),
        );
        $forest->train($training);

        $accuracy = (new Accuracy)->score($forest->predict($testing), $testing->labels());
        $this->info(sprintf('Classifier accuracy on holdout: %.1f%%', $accuracy * 100));

        app(ModelRepository::class)->saveClassifier($forest);
        $this->info('Saved resume_section_classifier.model');

        return round($accuracy * 100, 1);
    }

    /**
     * @param  list<list<float>>  $vectors
     */
    protected function trainClusterer(array $vectors): void
    {
        $k = (int) config('ml.k', 6);
        $this->line("Training K-Means (k={$k})...");

        $kmeans = new KMeans($k);
        $kmeans->train(Unlabeled::quick($vectors));

        app(ModelRepository::class)->saveClusterer($kmeans);
        $this->info('Saved resume_section_clusterer.model');
    }
}
