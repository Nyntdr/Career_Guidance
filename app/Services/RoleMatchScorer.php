<?php

namespace App\Services;

use App\Models\CareerRole;
use Illuminate\Support\Collection;

class RoleMatchScorer
{
    /**
     * Compute the deterministic skill-overlap score for a role against a
     * user's skill map (keyed by skill_id, values are UserSkill models).
     *
     * Essential skills carry 70% weight, preferred 30%; a skill only counts
     * as matched when the user's proficiency meets the role's minimum.
     *
     * @param Collection $userSkillMap
     * @return array{score:int, matched:array<int,string>, missing:array<int,string>}
     */
    public function overlap($userSkillMap, CareerRole $role): array
    {
        $required = $role->requiredSkills;

        if ($required->isEmpty()) {
            return ['score' => 0, 'matched' => [], 'missing' => []];
        }

        $essential = $required->filter(fn ($skill) => $skill->pivot->importance === 'essential');
        $preferred = $required->filter(fn ($skill) => $skill->pivot->importance === 'preferred');

        $essentialTotal = max($essential->count(), 1);
        $preferredTotal = max($preferred->count(), 1);

        $essentialMatched = 0;
        $preferredMatched = 0;
        $matched = [];
        $missing = [];

        foreach ($essential as $skill) {
            $user = $userSkillMap->get($skill->id);

            if ($user && $user->proficiency_level >= (int) $skill->pivot->min_proficiency) {
                $essentialMatched++;
                $matched[] = $skill->id;
            } else {
                $missing[] = $skill->id;
            }
        }

        foreach ($preferred as $skill) {
            if ($userSkillMap->has($skill->id)) {
                $preferredMatched++;
                $matched[] = $skill->id;
            }
        }

        $score = (int) round(
            ($essentialMatched / $essentialTotal) * 70
            + ($preferredMatched / $preferredTotal) * 30
        );

        return [
            'score' => $score,
            'matched' => array_values(array_unique($matched)),
            'missing' => array_values(array_unique($missing)),
        ];
    }
}