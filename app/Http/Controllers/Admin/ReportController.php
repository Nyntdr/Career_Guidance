<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerRole;
use App\Models\CareerRoleSkill;
use App\Models\Profile;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $inDemandSkills = Skill::select('skills.id', 'skills.name')
            ->selectRaw('count(career_role_skills.skill_id) as role_count')
            ->join('career_role_skills', 'skills.id', '=', 'career_role_skills.skill_id')
            ->groupBy('skills.id', 'skills.name')
            ->orderByDesc('role_count')
            ->limit(20)
            ->get();

        $profileCount = Profile::count();
        $totalCandidates = \App\Models\User::where('role', 'user')->count();

        $onboardingStats = [
            'total_candidates' => $totalCandidates,
            'has_profile' => $profileCount,
            'profile_rate' => $totalCandidates > 0 ? round($profileCount / $totalCandidates * 100) : 0,
            'has_education' => \App\Models\Education::distinct('profile_id')->count('profile_id'),
            'has_experience' => \App\Models\WorkExperience::distinct('profile_id')->count('profile_id'),
            'has_skills' => \App\Models\UserSkill::distinct('profile_id')->count('profile_id'),
        ];

        $roleSkillsCount = CareerRole::withCount('skills')
            ->orderByDesc('skills_count')
            ->limit(10)
            ->get(['id', 'title', 'skills_count']);

        return Inertia::render('Admin/Reports/Index', [
            'inDemandSkills' => $inDemandSkills,
            'onboardingStats' => $onboardingStats,
            'roleSkillsCount' => $roleSkillsCount,
        ]);
    }
}
