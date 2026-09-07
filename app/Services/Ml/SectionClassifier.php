<?php

namespace App\Services\Ml;

use Rubix\ML\Datasets\Unlabeled;
use Throwable;

class SectionClassifier
{
    public function __construct(protected ModelRepository $repository) {}

    public function available(): bool
    {
        return $this->repository->hasClassifier();
    }

    /**
     * Predict a section label and confidence per line sample.
     *
     * @param  list<list<float>>  $vectors
     * @return array<int, array{label: string, score: float}>|null
     */
    public function predictMany(array $vectors): ?array
    {
        if ($vectors === [] || ! $this->available()) {
            return null;
        }

        try {
            $model = $this->repository->classifier();

            if (! $model) {
                return null;
            }

            $probabilities = $model->proba(Unlabeled::quick($vectors));
            $result = [];

            foreach ($probabilities as $i => $distribution) {
                arsort($distribution);
                $label = (string) array_key_first($distribution);
                $result[$i] = [
                    'label' => $label,
                    'score' => (float) ($distribution[$label] ?? 0.0),
                ];
            }

            return $result;
        } catch (Throwable) {
            return null;
        }
    }
}
