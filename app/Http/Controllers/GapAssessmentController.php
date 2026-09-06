<?php

namespace App\Http\Controllers;

use App\Models\CareerRole;
use App\Models\UserSkill;
use App\Services\SkillGapAnalyzer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class GapAssessmentController extends Controller
{
    public function run(Request $request): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $targetRole = $request->filled('target_role_id')
            ? CareerRole::findOrFail($request->integer('target_role_id'))
            : null;

        $userSkills = UserSkill::where('profile_id', $profile->id)
            ->with('skill')
            ->get();

        $userSkillMap = $userSkills->keyBy('skill_id');

        $assessment = app(SkillGapAnalyzer::class)->run($profile, $userSkillMap, $targetRole);

        if (!$assessment) {
            return redirect()->route('roadmap.index')
                ->with('error', 'No skill gaps or targets available to assess.');
        }

        return redirect()->route('roadmap.index')
            ->with('success', 'Skill-gap assessment complete. Review your roadmap to get started.');
    }
}