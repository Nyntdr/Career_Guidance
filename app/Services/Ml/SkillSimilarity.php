<?php

namespace App\Services\Ml;

use App\Models\CareerRole;
use Illuminate\Support\Collection;

class SkillSimilarity
{
    /**
     * Keyword-wise TF-IDF weighted cosine similarity between the user's
     * proficiency vector and a role's requirement vector, expressed as a
     * percentage. When no reliable signal exists the result is 0 — the same
     * behavior as an ML model whose embedding is unavailable.
     *
     * The user's proficiency only contributes when it meets the role's minimum
     * for that skill, mirroring RoleMatchScorer so the two tiers agree on what
     * "has this skill" means.
     *
     * @param  Collection  $userSkillMap
     * @param  array{df: array<string, int>, count: int}|null  $corpus  Pre-built
     *                                                                  corpus statistics from corpus(). Pass this when scoring many roles
     *                                                                  against the same corpus to avoid recomputing document frequencies.
     */
    public function score($userSkillMap, CareerRole $role, ?Collection $roleDocs = null, ?array $corpus = null): float
    {
        $required = $role->requiredSkills;

        if ($required->isEmpty()) {
            return 0.0;
        }

        $corpus ??= $this->corpus($roleDocs ?? collect([$role]));
        $df = $corpus['df'];
        $documentCount = $corpus['count'];

        $roleVector = [];
        $userVector = [];

        foreach ($required as $skill) {
            $term = $this->dimension($skill->name);
            $occurrences = max($df[$term] ?? 1, 1);
            $idf = 1 + log(max($documentCount, 1) / $occurrences);

            $minProficiency = (int) $skill->pivot->min_proficiency;
            $importance = $skill->pivot->importance === 'essential' ? 2 : 1;
            $roleTermWeight = $importance * $minProficiency;
            $roleVector[$term] = max($roleVector[$term] ?? 0.0, $roleTermWeight * $idf);

            $user = $userSkillMap->get($skill->id);
            $userProficiency = $user ? (int) $user->proficiency_level : 0;
            $userTermWeight = $userProficiency >= $minProficiency ? $userProficiency : 0;
            $userVector[$term] = max($userVector[$term] ?? 0.0, $userTermWeight * $idf);
        }

        return round($this->cosine($userVector, $roleVector) * 100, 2);
    }

    /**
     * Document frequencies and corpus size for a set of roles, computed once
     * and reusable across every role scored against that corpus.
     *
     * @return array{df: array<string, int>, count: int}
     */
    public function corpus(iterable $roles): array
    {
        $df = $this->documentFrequencies($roles);
        $count = 0;

        foreach ($roles as $role) {
            if ($role instanceof CareerRole) {
                $count++;
            }
        }

        return ['df' => $df, 'count' => max($count, 1)];
    }

    /**
     * Cosine similarity between two term-weight vectors.
     *
     * @param  array<string, float>  $a
     * @param  array<string, float>  $b
     */
    public function cosine(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $key => $value) {
            $value = (float) $value;
            if ($value == 0) {
                continue;
            }
            $normA += $value * $value;

            if (isset($b[$key]) && (float) $b[$key] != 0) {
                $dot += $value * (float) $b[$key];
            }
        }

        foreach ($b as $value) {
            $value = (float) $value;
            if ($value == 0) {
                continue;
            }
            $normB += $value * $value;
        }

        $denominator = sqrt($normA) * sqrt($normB);

        if ($denominator == 0.0) {
            return 0.0;
        }

        return (float) ($dot / $denominator);
    }

    /**
     * Count how many roles in the corpus reference each skill term.
     *
     * @return array<string, int>
     */
    public function documentFrequencies(iterable $roles): array
    {
        $df = [];

        foreach ($roles as $role) {
            if (! $role instanceof CareerRole) {
                continue;
            }

            $seen = [];

            foreach ($role->requiredSkills as $skill) {
                $term = $this->dimension($skill->name);

                if (! isset($seen[$term])) {
                    $seen[$term] = true;
                    $df[$term] = ($df[$term] ?? 0) + 1;
                }
            }
        }

        return $df;
    }

    protected function dimension(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? $name));
    }
}
