<?php

namespace App\Services;

use App\Models\CareerRole;
use App\Models\GapAssessment;
use App\Models\GapAssessmentItem;
use App\Models\GapRecommendation;
use App\Models\JobPosting;
use App\Models\LearningResource;
use App\Models\Profile;
use App\Models\ProgressTracking;

class SkillGapAnalyzer
{
    public function run(
        Profile $profile,
        $userSkillMap,
        ?CareerRole $targetRole = null,
        ?JobPosting $targetJobPosting = null,
    ): ?GapAssessment {
        if ($targetJobPosting) {
            $requirements = $targetJobPosting->requiredSkills()
                ->with('skill')
                ->get()
                ->map(fn ($req) => [
                    'skill_id' => $req->skill_id,
                    'skill_name' => $req->skill?->name,
                    'required_level' => $req->min_proficiency,
                    'importance' => $req->importance === 'required' ? 'essential' : 'preferred',
                ]);
        } else {
            $targetRole = $targetRole ?? $this->defaultTargetRole($profile, $userSkillMap);
            if (! $targetRole) {
                return null;
            }
            $requirements = $targetRole->requiredSkills()
                ->get()
                ->map(fn ($skill) => [
                    'skill_id' => $skill->id,
                    'skill_name' => $skill->name,
                    'required_level' => $skill->pivot->min_proficiency,
                    'importance' => $skill->pivot->importance,
                ]);
        }

        if ($requirements->isEmpty()) {
            return null;
        }

        $assessment = GapAssessment::create([
            'profile_id' => $profile->id,
            'target_role_id' => $targetJobPosting ? null : $targetRole?->id,
            'target_job_posting_id' => $targetJobPosting?->id,
            'overall_readiness_score' => 0,
        ]);

        $this->purgeStaleProgress($profile, $assessment);

        $gapSkills = collect();

        foreach ($requirements as $requirement) {
            $user = $userSkillMap->get($requirement['skill_id']);
            $currentLevel = $user ? (int) $user->proficiency_level : 0;
            $requiredLevel = (int) $requirement['required_level'];
            $gapSize = $requiredLevel - $currentLevel;

            if ($gapSize <= 0) {
                continue;
            }

            $item = GapAssessmentItem::create([
                'gap_assessment_id' => $assessment->id,
                'skill_id' => $requirement['skill_id'],
                'required_level' => $requiredLevel,
                'current_level' => $currentLevel,
                'gap_size' => $gapSize,
                'priority' => $requirement['importance'] === 'essential' ? 'high' : 'medium',
            ]);

            ProgressTracking::create([
                'profile_id' => $profile->id,
                'gap_assessment_item_id' => $item->id,
                'status' => 'not_started',
            ]);

            $gapSkills->push($requirement['skill_id']);
        }

        $assessment->update([
            'overall_readiness_score' => $this->readinessScore($requirements, $userSkillMap),
            'skills_fingerprint' => self::fingerprint($userSkillMap),
        ]);

        if ($gapSkills->isNotEmpty() && $assessment->items()->exists()) {
            $this->rankRecommendations($assessment, $gapSkills);
        }

        return $assessment;
    }

    /**
     * Signature of the user's current skill map so the roadmap can detect when
     * an assessment no longer reflects the profile.
     */
    public static function fingerprint($userSkillMap): string
    {
        $pairs = collect($userSkillMap)
            ->map(fn ($us) => $us->skill_id.':'.(int) $us->proficiency_level)
            ->sort()
            ->values()
            ->implode('|');

        return md5($pairs);
    }

    /**
     * Remove progress rows that belong to previous assessments of the same
     * profile, so a re-run cannot orphan stale roadmap state.
     */
    protected function purgeStaleProgress(Profile $profile, GapAssessment $current): void
    {
        $oldItemIds = GapAssessmentItem::query()
            ->where('gap_assessment_id', '!=', $current->id)
            ->whereHas('gapAssessment', fn ($q) => $q->where('profile_id', $profile->id))
            ->pluck('id');

        if ($oldItemIds->isNotEmpty()) {
            ProgressTracking::where('profile_id', $profile->id)
                ->whereIn('gap_assessment_item_id', $oldItemIds)
                ->delete();
        }
    }

    private function defaultTargetRole($profile, $userSkillMap): ?CareerRole
    {
        $roles = CareerRole::with('requiredSkills')->get();
        $best = null;
        $bestScore = -1;

        foreach ($roles as $role) {
            $required = $role->requiredSkills;
            if ($required->isEmpty()) {
                continue;
            }

            $essential = $required->filter(fn ($s) => $s->pivot->importance === 'essential');
            $preferred = $required->filter(fn ($s) => $s->pivot->importance === 'preferred');

            $essentialMatched = 0;
            $essentialTotal = max($essential->count(), 1);
            $preferredMatched = 0;
            $preferredTotal = max($preferred->count(), 1);

            foreach ($essential as $skill) {
                $user = $userSkillMap->get($skill->id);
                if ($user && $user->proficiency_level >= $skill->pivot->min_proficiency) {
                    $essentialMatched++;
                }
            }

            foreach ($preferred as $skill) {
                if ($userSkillMap->has($skill->id)) {
                    $preferredMatched++;
                }
            }

            $score = round(($essentialMatched / $essentialTotal) * 70 + ($preferredMatched / $preferredTotal) * 30);

            if ($score > $bestScore && $score > 0) {
                $bestScore = $score;
                $best = $role;
            }
        }

        return $best;
    }

    private function readinessScore($requirements, $userSkillMap): float
    {
        if ($requirements->isEmpty()) {
            return 0;
        }

        $numerator = 0;
        $denominator = 0;

        foreach ($requirements as $requirement) {
            $required = (int) $requirement['required_level'];
            $user = $userSkillMap->get($requirement['skill_id']);
            $current = min($user ? (int) $user->proficiency_level : 0, $required);

            $numerator += $current;
            $denominator += $required;
        }

        return round(($numerator / max($denominator, 1)) * 100, 2);
    }

    private function rankRecommendations(GapAssessment $assessment, $gapSkillIds): void
    {
        $gapSkillList = $gapSkillIds->values()->all();

        foreach ($assessment->items as $item) {
            $candidates = LearningResource::query()
                ->whereHas('skills', fn ($q) => $q->whereKey($item->skill_id))
                ->withCount(['skills' => fn ($q) => $q->whereIn('skills.id', $gapSkillList)])
                ->orderByDesc('skills_count')
                ->orderBy('cost')
                ->orderBy('duration_hours')
                ->take(3)
                ->get();

            foreach ($candidates as $index => $resource) {
                GapRecommendation::create([
                    'gap_assessment_item_id' => $item->id,
                    'learning_resource_id' => $resource->id,
                    'rank' => $index + 1,
                ]);
            }
        }
    }
}
