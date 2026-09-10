<?php

namespace App\Console\Commands;

use App\Services\Ml\ModelRepository;
use App\Services\Ml\ResumeLineVectorizer;
use Illuminate\Console\Command;

class MlStatus extends Command
{
    protected $signature = 'ml:status';

    protected $description = 'Show resume-section ML model status and last training details';

    public function handle(ModelRepository $repository): int
    {
        $state = [
            'Random Forest classifier' => $repository->hasClassifier() ? 'trained' : 'missing',
            'K-Means clusterer' => $repository->hasClusterer() ? 'trained' : 'missing',
            'Vocabulary' => $repository->hasVocab() ? $repository->vocabPath().' ('.count($repository->vocab()).' terms)' : 'missing',
            'Meta file' => $repository->metaPath(),
        ];

        foreach ($state as $label => $value) {
            $this->line(str_pad($label, 26, ' ', STR_PAD_RIGHT).': '.$value);
        }

        $meta = $repository->meta();

        if ($meta === []) {
            $this->warn('No training metadata found — run `php artisan ml:train` to record it.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Last training:');

        foreach ($meta as $key => $value) {
            $this->line('  '.str_pad($key, 25, ' ', STR_PAD_RIGHT).': '.$value);
        }

        $corpusPath = $repository->corpusPath();

        if (is_file($corpusPath)) {
            $corpus = json_decode((string) file_get_contents($corpusPath), true);
            $count = is_array($corpus) ? count($corpus['samples'] ?? []) : 0;
            $this->line('  '.str_pad('corpus_file_sources', 25, ' ', STR_PAD_RIGHT).': '.$corpusPath);
            $this->line('  '.str_pad('corpus_file_samples', 25, ' ', STR_PAD_RIGHT).': '.$count);
        }

        $this->newLine();
        $this->line('Base vocabulary size: '.count(ResumeLineVectorizer::BASE_VOCAB).' (always available even without an ML model)');
        $this->warn('Classifier holdout accuracy is typically low; dictionary/structural heuristics carry resume parsing.');

        return self::SUCCESS;
    }
}
