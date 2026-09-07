<?php

namespace App\Services\Ml;

use Rubix\ML\Datasets\Unlabeled;
use Throwable;

class SectionClusterer
{
    public function __construct(protected ModelRepository $repository) {}

    public function available(): bool
    {
        return $this->repository->hasClusterer();
    }

    /**
     * Assign a cluster id to every line sample (K-Means segmentation signal).
     *
     * @param  list<list<float>>  $vectors
     * @return list<int>|null
     */
    public function clusterMany(array $vectors): ?array
    {
        if ($vectors === [] || ! $this->available()) {
            return null;
        }

        try {
            $model = $this->repository->clusterer();

            if (! $model) {
                return null;
            }

            return $model->predict(Unlabeled::quick($vectors));
        } catch (Throwable) {
            return null;
        }
    }
}
