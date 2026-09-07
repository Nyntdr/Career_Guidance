<?php

namespace App\Services\Ml;

use Rubix\ML\Estimator;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Throwable;

class ModelRepository
{
    public function directory(): string
    {
        return (string) config('ml.model_path');
    }

    public function trainingDirectory(): string
    {
        return (string) config('ml.training_path');
    }

    public function classifierPath(): string
    {
        return $this->directory().'/resume_section_classifier.model';
    }

    public function clustererPath(): string
    {
        return $this->directory().'/resume_section_clusterer.model';
    }

    public function vocabPath(): string
    {
        return (string) config('ml.vocab_path');
    }

    public function corpusPath(): string
    {
        return $this->trainingDirectory().'/'.config('ml.corpus_file', 'section_lines.json');
    }

    public function isReady(): bool
    {
        return $this->hasClassifier() && $this->hasClusterer() && $this->hasVocab();
    }

    public function hasClassifier(): bool
    {
        return is_file($this->classifierPath());
    }

    public function hasClusterer(): bool
    {
        return is_file($this->clustererPath());
    }

    public function hasVocab(): bool
    {
        return is_file($this->vocabPath());
    }

    public function classifier(): ?Estimator
    {
        return $this->load($this->classifierPath());
    }

    public function clusterer(): ?Estimator
    {
        return $this->load($this->clustererPath());
    }

    public function vocab(): array
    {
        if (! $this->hasVocab()) {
            return ResumeLineVectorizer::BASE_VOCAB;
        }

        try {
            $decoded = json_decode((string) file_get_contents($this->vocabPath()), true);
        } catch (Throwable) {
            return ResumeLineVectorizer::BASE_VOCAB;
        }

        return is_array($decoded) && array_is_list($decoded) ? $decoded : ResumeLineVectorizer::BASE_VOCAB;
    }

    public function saveClassifier(Estimator $estimator): void
    {
        $this->save($estimator, $this->classifierPath());
    }

    public function saveClusterer(Estimator $estimator): void
    {
        $this->save($estimator, $this->clustererPath());
    }

    public function saveVocab(array $vocab): void
    {
        $this->ensureDirectories();

        file_put_contents($this->vocabPath(), json_encode(array_values($vocab), JSON_PRETTY_PRINT));
    }

    public function ensureDirectories(): void
    {
        foreach ([$this->directory(), $this->trainingDirectory()] as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
        }
    }

    protected function load(string $path): ?Estimator
    {
        if (! is_file($path)) {
            return null;
        }

        try {
            return PersistentModel::load(new Filesystem($path))->base();
        } catch (Throwable) {
            return null;
        }
    }

    protected function save(Estimator $estimator, string $path): void
    {
        $estimator = new PersistentModel($estimator, new Filesystem($path));
        $estimator->save();
    }
}
