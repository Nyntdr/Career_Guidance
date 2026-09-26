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
     * Essential skills carry the essential weight (default 70%), preferred the
     * preferred weight (default 30%); a skill only counts as matched when the
     * user's proficiency meets the requirement's minimum.
     *
     * @param  Collection  $userSkillMap
     * @return array{score:int, matched:array<int,string>, missing:array<int,string>}
     */
    public function overlap($userSkillMap, CareerRole $role): array
    {
        return $this->score($userSkillMap, $role->requiredSkills);
    }

    /**
     * The same weighted overlap score for an arbitrary requirement set.
     *
     * Accepts either pivot-style rows (a `pivot` carrying `importance` and
     * `min_proficiency`, i.e. CareerRole::requiredSkills) or job-posting rows
     * (properties directly on the model, i.e. JobRequiredSkill) — the two
     * shapes only differ in where those two values live.
     *
     * A requirement counts as matched when the user holds the skill at or above
     * its minimum proficiency (essential), or holds it at any level (preferred).
     *
     * @param  Collection<int, mixed>  $requirements
     * @return array{score:int, matched:array<int,string>, missing:array<int,string>}
     */
    public function score($userSkillMap, $requirements): array
    {
        if ($requirements->isEmpty()) {
            return ['score' => 0, 'matched' => [], 'missing' => []];
        }

        $satisfied = [];

        foreach ($requirements as $skill) {
            $user = $userSkillMap->get($this->skillId($skill));

            if (! $user) {
                continue;
            }

            // Essential skills must be met at the required proficiency;
            // preferred skills only need to be present at all.
            $isSatisfied = $this->importance($skill) === 'essential'
                ? $user->proficiency_level >= $this->minProficiency($skill)
                : true;

            if ($isSatisfied) {
                $satisfied[$this->skillId($skill)] = true;
            }
        }

        return $this->aggregate($requirements, $satisfied);
    }

    /**
     * Aggregate a precomputed set of satisfied requirements into the shared
     * essential/preferred weighted score.
     *
     * Used by callers whose notion of "satisfied" is not the user's skill
     * profile — the ATS keyword score, for instance, treats a requirement as
     * satisfied when the term (or an alias) literally appears in the resume
     * text. The weighting stays identical so the three subsystems cannot drift.
     *
     * @param  Collection<int, mixed>  $requirements
     * @param  array<array-key, bool>  $satisfied  Map of skill_id => true
     * @return array{score:int, matched:array<int,string>, missing:array<int,string>}
     */
    public function aggregate($requirements, array $satisfied): array
    {
        if ($requirements->isEmpty()) {
            return ['score' => 0, 'matched' => [], 'missing' => []];
        }

        $essential = $requirements->filter(fn ($item) => $this->importance($item) === 'essential');
        $preferred = $requirements->filter(fn ($item) => $this->importance($item) === 'preferred');

        $weights = $this->weights();
        $essentialTotal = max($essential->count(), 1);
        $preferredTotal = max($preferred->count(), 1);

        $essentialMatched = 0;
        $preferredMatched = 0;
        $matched = [];
        $missing = [];

        foreach ($essential as $skill) {
            if (isset($satisfied[$this->skillId($skill)])) {
                $essentialMatched++;
                $matched[] = $this->skillId($skill);
            } else {
                $missing[] = $this->skillId($skill);
            }
        }

        // Preferred skills only require the skill to be present at all.
        foreach ($preferred as $skill) {
            if (isset($satisfied[$this->skillId($skill)])) {
                $preferredMatched++;
                $matched[] = $this->skillId($skill);
            }
        }

        $score = (int) round(
            ($essentialMatched / $essentialTotal) * $weights['essential']
            + ($preferredMatched / $preferredTotal) * $weights['preferred']
        );

        return [
            'score' => $score,
            'matched' => array_values(array_unique($matched)),
            'missing' => array_values(array_unique($missing)),
        ];
    }

    /**
     * @return array{essential: int, preferred: int}
     */
    public function weights(): array
    {
        return [
            'essential' => (int) config('scoring.essential_weight', 70),
            'preferred' => (int) config('scoring.preferred_weight', 30),
        ];
    }

    /**
     * Career roles use `essential`/`preferred`; job postings use
     * `required`/`preferred`. Both collapse onto the essential/preferred split.
     */
    private function importance(mixed $item): string
    {
        $raw = isset($item->pivot) ? $item->pivot->importance : $item->importance;

        return $raw === 'essential' || $raw === 'required' ? 'essential' : 'preferred';
    }

    /**
     * The id of the skill a requirement refers to.
     *
     * CareerRole::requiredSkills() yields Skill models (id is the skill);
     * JobPosting::requiredSkills() yields JobRequiredSkill pivot rows whose own
     * uuid PK differs from skill_id — conflating the two silently zeroes every
     * score, so resolve it in one place.
     */
    private function skillId(mixed $item): string
    {
        return isset($item->pivot) ? (string) $item->id : (string) $item->skill_id;
    }

    private function minProficiency(mixed $item): int
    {
        $raw = isset($item->pivot) ? $item->pivot->min_proficiency : $item->min_proficiency;

        return (int) $raw;
    }
}
