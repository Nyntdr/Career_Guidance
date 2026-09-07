<?php

namespace App\Console\Commands;

use App\Models\ResumeSection;
use App\Services\Ml\ModelRepository;
use App\Services\Ml\ResumeLineVectorizer;
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
        {--include-resumes : Also harvest weak labels from stored resumes}';

    protected $description = 'Train the resume-section Random Forest classifier and K-Means clusterer';

    protected const VALID_LABELS = [
        'experience', 'skills', 'education', 'certifications', 'projects',
        'publications', 'awards', 'volunteer', 'languages', 'interests',
        'references', 'summary', 'contact',
    ];

    public function handle(ModelRepository $repository): int
    {
        $model = $this->option('model') ?? 'all';

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

        if ($model === 'all' || $model === 'classifier') {
            $this->trainClassifier($vectors, $labels);
        }

        if ($model === 'all' || $model === 'clusterer') {
            $this->trainClusterer($vectors);
        }

        return self::SUCCESS;
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

        return [$samples, $labels, $lines];
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
    protected function trainClassifier(array $vectors, array $labels): void
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
