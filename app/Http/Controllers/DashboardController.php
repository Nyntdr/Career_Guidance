<?php

namespace App\Http\Controllers;

use App\Models\CareerRecommendation;
use App\Models\CareerRole;
use App\Models\Education;
use App\Models\GapAssessment;
use App\Models\ProgressTracking;
use App\Models\UserSkill;
use App\Models\WorkExperience;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $role = $request->user()->role->value;

        if ($role !== 'candidate') {
            $controller = match ($role) {
                'admin' => \App\Http\Controllers\Admin\DashboardController::class,
                'counselor' => \App\Http\Controllers\Counselor\DashboardController::class,
                default => null,
            };

            if ($controller) {
                return app($controller)->__invoke($request);
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
                'items' => fn($q) => $q->with('skill'),
            ])
            ->latest()
            ->first();

        $progressCount = ProgressTracking::where('profile_id', $profile->id)->count();

        if ($recommendations->isEmpty()) {
            $computed = $this->computeMatches($userSkills, $userSkillIds);

            $recommendations = collect($computed);
            $topMatch = $computed[0] ?? null;
            $careerMatches = $computed;
            $avgCompatibility = count($computed) > 0
                ? round(collect($computed)->avg('compatibility'))
                : 0;
            $careerPathsCount = count($computed);
        } else {
            $topRec = $recommendations->first();
            $topMatch = $topRec ? [
                'title' => $topRec->careerRole?->title ?? 'Unknown',
                'compatibility' => (int) round($topRec->match_score),
                'description' => $topRec->rationale ?? $topRec->careerRole?->description ?? '',
                'salaryRange' => $this->formatSalary($topRec->careerRole?->avg_salary_range),
                'growth' => $topRec->careerRole?->growth_outlook ?? '',
            ] : null;

            $careerMatches = $recommendations->map(fn($r) => [
                'title' => $r->careerRole?->title ?? 'Unknown',
                'score' => (int) round($r->match_score),
                'category' => $r->careerRole?->typical_seniority ?? 'General',
            ])->toArray();

            $avgCompatibility = $recommendations->count() > 0
                ? (int) round($recommendations->avg('match_score'))
                : 0;
            $careerPathsCount = $recommendations->count();
        }

        if ($gapAssessment && $gapAssessment->items->isNotEmpty()) {
            $skillsToDevelop = $gapAssessment->items->map(fn($item) => [
                'name' => $item->skill?->name ?? 'Unknown',
                'priority' => $item->priority ?? 'Medium',
                'current' => min(100, ($item->current_level / max($item->required_level, 1)) * 100),
                'target' => 100,
            ])->values()->toArray();
        } else {
            $skillsToDevelop = $this->computeGaps($userSkills, $userSkillIds);
        }

        $profileSummary = [
            'education' => $education->isNotEmpty()
                ? $education->first()->degree . ' in ' . $education->first()->field_of_study . ' at ' . $education->first()->institution
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
            'profileSummary' => $profileSummary,
            'recentActivity' => $recentActivity,
        ]);
    }

    private function computeMatches($userSkills, $userSkillIds): array
    {
        $userSkillMap = $userSkills->keyBy('skill_id');
        $roles = CareerRole::with('requiredSkills')->get();
        $matches = [];

        foreach ($roles as $role) {
            $required = $role->requiredSkills;
            if ($required->isEmpty()) {
                continue;
            }

            $essential = $required->filter(fn($s) => $s->pivot->importance === 'essential');
            $preferred = $required->filter(fn($s) => $s->pivot->importance === 'preferred');

            $essentialMatched = 0;
            $essentialTotal = max($essential->count(), 1);
            $preferredMatched = 0;
            $preferredTotal = max($preferred->count(), 1);

            foreach ($essential as $skill) {
                if ($userSkillMap->has($skill->id)) {
                    $userProficiency = $userSkillMap->get($skill->id)->proficiency_level;
                    if ($userProficiency >= $skill->pivot->min_proficiency) {
                        $essentialMatched++;
                    }
                }
            }

            foreach ($preferred as $skill) {
                if ($userSkillMap->has($skill->id)) {
                    $preferredMatched++;
                }
            }

            $essentialRate = $essentialMatched / $essentialTotal;
            $preferredRate = $preferredMatched / $preferredTotal;

            $score = round(($essentialRate * 70 + $preferredRate * 30));

            if ($score > 0) {
                $matches[] = [
                    'title' => $role->title,
                    'score' => $score,
                    'category' => $role->typical_seniority ?? 'General',
                    'compatibility' => $score,
                ];
            }
        }

        usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($matches, 0, 10);
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

                if (!$hasSkill || $userProf < $minProf) {
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

        usort($gaps, fn($a, $b) => ['High' => 0, 'Medium' => 1, 'Low' => 2][$a['priority']]
            <=> ['High' => 0, 'Medium' => 1, 'Low' => 2][$b['priority']]);

        return array_values(array_slice($gaps, 0, 10));
    }

    private function buildRecentActivity($profile, $userSkills, $education, $workExperiences): array
    {
        $activities = [];

        foreach ($userSkills as $us) {
            $activities[] = [
                'action' => 'Added skill: ' . ($us->skill?->name ?? 'Unknown'),
                'date' => $us->created_at->diffForHumans(),
            ];
        }

        foreach ($education as $edu) {
            $activities[] = [
                'action' => 'Added education: ' . $edu->institution,
                'date' => $edu->created_at->diffForHumans(),
            ];
        }

        foreach ($workExperiences as $work) {
            $activities[] = [
                'action' => 'Added work experience: ' . $work->title . ' at ' . $work->company,
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

        usort($activities, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));

        return array_slice($activities, 0, 8);
    }

    private function formatSalary($range): string
    {
        if (!$range || !isset($range['min'])) {
            return 'Not specified';
        }
        $min = '$' . number_format($range['min']);
        $max = isset($range['max']) ? '$' . number_format($range['max']) : '';
        return $max ? "{$min} – {$max}" : $min;
    }
}
