<?php

namespace App\Services;

use App\Models\CareerPath;
use App\Models\CareerRecommendation;
use App\Models\CareerRole;
use App\Models\Profile;
use App\Services\Ml\SkillSimilarity;
use App\Support\Settings;
use Illuminate\Support\Collection;

class RecommendationEngine
{
    public function __construct(
        protected RoleMatchScorer $scorer,
        protected SkillSimilarity $similarity,
        protected RecommendationRationalizer $rationalizer,
    ) {
    }

    /**
     * Regenerate career recommendations for a profile. The stored snapshot is
     * wiped and recreated (no history). Returns the newly created rows, or an
     * empty collection when there is nothing to score against.
     */
    public function generate(Profile $profile): Collection
    {
        CareerRecommendation::where('profile_id', $profile->id)->delete();

        $userSkills = $profile->userSkills()->with('skill')->get();

        if ($userSkills->isEmpty()) {
            return collect();
        }

        $roles = CareerRole::with('requiredSkills')->get();

        if ($roles->isEmpty()) {
            return collect();
        }

        $userSkillMap = $userSkills->keyBy('skill_id');

        $weights = (array) Settings::get('recommendation_weights', config('recommendations.weights'));
        $weightOverlap = (float) ($weights['overlap'] ?? 60) / 100;
        $weightSemantic = (float) ($weights['semantic'] ?? 40) / 100;

        $scored = [];

        foreach ($roles as $role) {
            $overlap = $this->scorer->overlap($userSkillMap, $role);
            $semantic = $this->similarity->score($userSkillMap, $role, $roles);

            $scored[$role->id] = [
                'role' => $role,
                'overlap' => $overlap,
                'semantic' => $semantic,
                'score' => $overlap['score'] * $weightOverlap + $semantic * $weightSemantic,
            ];
        }

        $this->applyPathBoost($scored);

        uasort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        $topCount = max((int) Settings::get('top_matches', config('settings.definitions.top_matches.default', 10)), 1);
        $topIds = array_slice(array_keys($scored), 0, $topCount);

        $created = collect();

        foreach ($topIds as $index => $roleId) {
            $row = $scored[$roleId];

            if ($index === 0) {
                $rationale = $this->rationalizer->for($profile, $row['role'], $row['overlap']);
            } else {
                $rationale = [
                    'text' => $this->rationalizer->template($profile, $row['role'], $row['overlap']),
                    'source' => 'template',
                ];
            }

            $created->push(CareerRecommendation::create([
                'profile_id' => $profile->id,
                'career_role_id' => $row['role']->id,
                'match_score' => round($row['score'], 2),
                'rationale' => $rationale['text'],
                'rationale_source' => $rationale['source'],
                'generated_at' => now(),
            ]));
        }

        return $created;
    }

    /**
     * Add a small, hop-decayed bonus to roles reachable via career_paths from
     * the best direct matches. This surfaces "next step" roles without
     * polluting already-strong matches.
     *
     * @param array<int, array{score: float}> $scored
     */
    protected function applyPathBoost(array &$scored): void
    {
        $bonus = (float) Settings::get('recommendation_path_bonus', config('recommendations.path_bonus', 5));

        if ($bonus <= 0) {
            return;
        }

        $adjacency = [];

        foreach (CareerPath::query()->get(['from_role_id', 'to_role_id']) as $path) {
            $adjacency[$path->from_role_id][] = $path->to_role_id;
        }

        if ($adjacency === []) {
            return;
        }

        $seeds = [];

        foreach ($scored as $roleId => $row) {
            if ($row['score'] > 0) {
                $seeds[] = $roleId;
            }
        }

        foreach ($this->reachable($seeds, $adjacency) as $roleId => $hops) {
            if (! isset($scored[$roleId])) {
                continue;
            }

            $scored[$roleId]['score'] = min(100, $scored[$roleId]['score'] + $bonus / $hops);
        }
    }

    /**
     * Breadth-first reachability (max two hops) over the career path graph.
     * Seeds themselves are excluded so direct matches keep their raw score.
     *
     * @param array<int, mixed> $seeds
     * @param array<int, array<int, string>> $adjacency
     * @return array<int, int> role id => hop distance
     */
    protected function reachable(array $seeds, array $adjacency): array
    {
        if ($seeds === []) {
            return [];
        }

        $reachable = [];
        $maxHops = 2;

        foreach ($seeds as $seed) {
            $queue = [[$seed, 0]];
            $visited = [$seed => true];

            while ($queue !== []) {
                [$current, $hops] = array_shift($queue);

                if ($hops >= $maxHops) {
                    continue;
                }

                foreach ($adjacency[$current] ?? [] as $next) {
                    if (isset($visited[$next])) {
                        continue;
                    }

                    $visited[$next] = true;
                    $distance = $hops + 1;

                    if (! isset($reachable[$next]) || $distance < $reachable[$next]) {
                        $reachable[$next] = $distance;
                    }

                    $queue[] = [$next, $distance];
                }
            }
        }

        return $reachable;
    }
}