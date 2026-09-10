<?php

namespace App\Http\Controllers;

use App\Models\CareerRecommendation;
use App\Models\CareerRole;
use App\Models\Education;
use App\Models\GapAssessment;
use App\Models\ProgressTracking;
use App\Models\UserSkill;
use App\Models\WorkExperience;
use App\Services\RecommendationEngine;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $role = $request->user()->role->value;

        if ($role !== 'user') {
            if ($role === 'admin') {
                return app(Admin\DashboardController::class)->__invoke($request);
            }
        }

        $user = $request->user();
        $profile = $user->profile()->firstOrCreate([]);

        $userSkills = UserSkill::where('profile_id', $profile->id)
            ->with('skill')
            ->get();

        $totalUserSkills = $userSkills->count();
        $userSkillIds = $userSkills->pluck('skill_id');

        $education = Education::where('profile_id', $profile->id)
            ->orderByDesc('end_date')
            ->get();

        $workExperiences = WorkExperience::where('profile_id', $profile->id)
            ->orderByDesc('start_date')
            ->get();

        $recommendations = CareerRecommendation::where('profile_id', $profile->id)
            ->with('careerRole')
            ->orderByDesc('match_score')
            ->get();

        $gapAssessment = GapAssessment::where('profile_id', $profile->id)
            ->with([
                'targetRole',
                'items' => fn ($q) => $q->with('skill'),
            ])
            ->latest()
            ->first();

        $progressCount = ProgressTracking::where('profile_id', $profile->id)->count();

        if ($recommendations->isEmpty()) {
            $lock = Cache::lock('rec-generate:'.$profile->id, 120);

            if ($lock->get()) {
                app(RecommendationEngine::class)->generate($profile);
                $lock->release();
            }

            $recommendations = CareerRecommendation::where('profile_id', $profile->id)
                ->with('careerRole')
                ->orderByDesc('match_score')
                ->get();
        }

        $topRec = $recommendations->first();
        $topMatch = $topRec ? [
            'title' => $topRec->careerRole?->title ?? 'Unknown',
            'compatibility' => (int) round($topRec->match_score),
            'description' => $topRec->rationale ?? $topRec->careerRole?->description ?? '',
            'salaryRange' => $this->formatSalary($topRec->careerRole?->avg_salary_range),
            'growth' => $topRec->careerRole?->growth_outlook ?? '',
        ] : null;

        $careerMatches = $recommendations->map(fn ($r) => [
            'title' => $r->careerRole?->title ?? 'Unknown',
            'score' => (int) round($r->match_score),
            'category' => $r->careerRole?->typical_seniority ?? 'General',
        ])->toArray();

        $avgCompatibility = $recommendations->count() > 0
            ? (int) round($recommendations->avg('match_score'))
            : 0;
        $careerPathsCount = $recommendations->count();

        $hasGapInsights = false;

        if ($gapAssessment && $gapAssessment->items->isNotEmpty()) {
            $hasGapInsights = true;
            $skillsToDevelop = $gapAssessment->items->map(fn ($item) => [
                'name' => $item->skill?->name ?? 'Unknown',
                'priority' => $item->priority ?? 'Medium',
                'current' => min(100, ($item->current_level / max($item->required_level, 1)) * 100),
                'target' => 100,
            ])->values()->toArray();
        } elseif ($topRec !== null && (float) $topRec->match_score > 0) {
            $hasGapInsights = true;
            $skillsToDevelop = $this->computeGaps($userSkills, $userSkillIds);
        } else {
            $skillsToDevelop = [];
        }

        $profileSummary = [
            'education' => $education->isNotEmpty()
                ? $education->first()->degree.' in '.$education->first()->field_of_study.' at '.$education->first()->institution
                : 'Not specified',
            'interests' => $profile->target_industries ?? $profile->target_roles ?? ['Not specified'],
            'goals' => $profile->headline ?? ($profile->bio ? substr($profile->bio, 0, 100) : 'Set your career goal in your profile'),
        ];

        $recentActivity = $this->buildRecentActivity($profile, $userSkills, $education, $workExperiences);

        return Inertia::render('Dashboard', [
            'stats' => [
                'careerPaths' => $careerPathsCount,
                'avgCompatibility' => $avgCompatibility,
                'skillsAssessed' => $totalUserSkills,
                'roadmapSteps' => $progressCount,
            ],
            'topMatch' => $topMatch,
            'careerMatches' => $careerMatches,
            'skillsToDevelop' => $skillsToDevelop,
            'gapInsights' => $hasGapInsights,
            'profileSummary' => $profileSummary,
            'recentActivity' => $recentActivity,
        ]);
    }

    private function computeGaps($userSkills, $userSkillIds): array
    {
        $userSkillMap = $userSkills->keyBy('skill_id');
        $gaps = [];

        $roles = CareerRole::with('requiredSkills')->get();

        foreach ($roles as $role) {
            foreach ($role->requiredSkills as $skill) {
                $hasSkill = $userSkillMap->has($skill->id);
                $userProf = $hasSkill ? $userSkillMap->get($skill->id)->proficiency_level : 0;
                $minProf = $skill->pivot->min_proficiency;

                if (! $hasSkill || $userProf < $minProf) {
                    $gap = ($minProf - $userProf) / $minProf;
                    $priority = match ($skill->pivot->importance) {
                        'essential' => 'High',
                        'preferred' => 'Medium',
                        default => 'Low',
                    };

                    $gaps[$skill->name] = [
                        'name' => $skill->name,
                        'priority' => $priority,
                        'current' => min(100, ($userProf / max($minProf, 1)) * 100),
                        'target' => 100,
                    ];
                }
            }
        }

        usort($gaps, fn ($a, $b) => ['High' => 0, 'Medium' => 1, 'Low' => 2][$a['priority']]
            <=> ['High' => 0, 'Medium' => 1, 'Low' => 2][$b['priority']]);

        return array_values(array_slice($gaps, 0, Settings::get('top_matches', 10)));
    }

    private function buildRecentActivity($profile, $userSkills, $education, $workExperiences): array
    {
        $activities = [];

        foreach ($userSkills as $us) {
            $activities[] = [
                'action' => 'Added skill: '.($us->skill?->name ?? 'Unknown'),
                'date' => $us->created_at->diffForHumans(),
            ];
        }

        foreach ($education as $edu) {
            $activities[] = [
                'action' => 'Added education: '.$edu->institution,
                'date' => $edu->created_at->diffForHumans(),
            ];
        }

        foreach ($workExperiences as $work) {
            $activities[] = [
                'action' => 'Added work experience: '.$work->title.' at '.$work->company,
                'date' => $work->created_at->diffForHumans(),
            ];
        }

        if ($profile->created_at && $profile->created_at->eq($profile->updated_at)) {
            $activities[] = [
                'action' => 'Created career profile',
                'date' => $profile->created_at->diffForHumans(),
            ];
        } elseif ($profile->updated_at) {
            $activities[] = [
                'action' => 'Updated career profile',
                'date' => $profile->updated_at->diffForHumans(),
            ];
        }

        usort($activities, fn ($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));

        return array_slice($activities, 0, 8);
    }

    private function formatSalary($range): string
    {
        if (! $range || ! isset($range['min'])) {
            return 'Not specified';
        }

        $symbol = Settings::get('currency_symbol', '$');
        $position = Settings::get('currency_position', 'before');

        $fmt = fn ($n) => $position === 'after'
            ? number_format($n).$symbol
            : $symbol.number_format($n);

        $min = $fmt($range['min']);
        $max = isset($range['max']) ? $fmt($range['max']) : '';

        return $max ? "{$min} – {$max}" : $min;
    }
}
