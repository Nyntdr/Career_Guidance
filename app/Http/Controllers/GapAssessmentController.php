<?php

namespace App\Http\Controllers;

use App\Models\CareerRole;
use App\Models\GapAssessment;
use App\Models\UserSkill;
use App\Services\SkillGapAnalyzer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GapAssessmentController extends Controller
{
    public function run(\App\Http\Requests\StoreGapAssessmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $profile = $request->user()->profile()->firstOrCreate([]);

        $targetRole = null;
        $targetRoleId = $request->input('target_role_id');

        if ($targetRoleId) {
            $targetRole = CareerRole::findOrFail($targetRoleId);
        } else {
            $previous = GapAssessment::where('profile_id', $profile->id)->latest()->first();

            if ($previous?->target_role_id) {
                $targetRole = CareerRole::find($previous->target_role_id);
            }
        }

        $userSkills = UserSkill::where('profile_id', $profile->id)
            ->with('skill')
            ->get();

        $userSkillMap = $userSkills->keyBy('skill_id');

        $assessment = app(SkillGapAnalyzer::class)->run($profile, $userSkillMap, $targetRole);

        if (! $assessment) {
            return redirect()->route('roadmap.index')
                ->with('error', 'Add skills or choose a target career role to run a gap assessment.');
        }

        return redirect()->route('roadmap.index')
            ->with('success', 'Skill-gap assessment complete. Review your roadmap to get started.');
    }
}
