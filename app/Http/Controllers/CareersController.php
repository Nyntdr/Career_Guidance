<?php

namespace App\Http\Controllers;

use App\Models\CareerRole;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CareersController extends Controller
{
    public function index(Request $request): Response
    {
        $roles = CareerRole::query()
            ->withCount('requiredSkills')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search'));

                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('title')
            ->paginate(9)
            ->withQueryString();

        $profile = $request->user()?->profile;
        $roleIds = $roles->pluck('id');
        $matchScores = $profile
            ? \App\Models\CareerRecommendation::where('profile_id', $profile->id)
                ->whereIn('career_role_id', $roleIds)
                ->pluck('match_score', 'career_role_id')
            : collect();

        $roles->getCollection()->transform(function ($role) use ($matchScores) {
            $role->user_match_score = $matchScores->get($role->id);
            return $role;
        });

        return Inertia::render('Careers/Index', [
            'roles' => $roles,
            'filters' => ['search' => $request->string('search')->toString() ?? ''],
        ]);
    }

    public function show(Request $request, CareerRole $careerRole): Response
    {
        $careerRole->load(['requiredSkills' => function ($query) {
            $query->orderBy('career_role_skills.importance')
                ->orderBy('career_role_skills.min_proficiency', 'desc');
        }]);

        $requiredSkills = collect($careerRole->requiredSkills)->map(function ($skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'importance' => $skill->pivot->importance,
                'min_proficiency' => $skill->pivot->min_proficiency,
            ];
        });

        $skillsByImportance = [
            'essential' => $requiredSkills->where('importance', 'essential')->values(),
            'preferred' => $requiredSkills->where('importance', 'preferred')->values(),
        ];

        $skillCount = $careerRole->requiredSkills->count();

        $careerRole->load([
            'outgoingPaths.toRole',
            'incomingPaths.fromRole',
        ]);

        $outgoingPaths = $careerRole->outgoingPaths->map(function ($path) {
            return [
                'id' => $path->id,
                'to_role' => $path->toRole?->title ?? 'Unknown',
                'to_role_id' => $path->toRole?->id,
                'typical_years' => $path->typical_years,
                'notes' => $path->notes,
            ];
        })->values();

        $incomingPaths = $careerRole->incomingPaths->map(function ($path) {
            return [
                'id' => $path->id,
                'from_role' => $path->fromRole?->title ?? 'Unknown',
                'from_role_id' => $path->fromRole?->id,
                'typical_years' => $path->typical_years,
                'notes' => $path->notes,
            ];
        })->values();

        $relatedRoles = CareerRole::query()
            ->where('id', '!=', $careerRole->id)
            ->withCount('requiredSkills')
            ->orderBy('title')
            ->take(4)
            ->get();

        $profile = $request->user()?->profile;
        $userSkills = $profile ? $profile->userSkills()->with('skill')->get() : collect();
        $userSkillMap = $userSkills->keyBy('skill_id');
        $hasSkills = $userSkillMap->isNotEmpty();

        $scorer = app(\App\Services\RoleMatchScorer::class);
        $overlap = $scorer->overlap($userSkillMap, $careerRole);

        $matchedSkills = \App\Models\Skill::whereIn('id', $overlap['matched'])->pluck('name')->values()->all();
        $missingSkills = \App\Models\Skill::whereIn('id', $overlap['missing'])->pluck('name')->values()->all();

        $assessment = $profile
            ? \App\Models\GapAssessment::where('profile_id', $profile->id)
                ->where('target_role_id', $careerRole->id)
                ->with(['items.skill'])
                ->latest()
                ->first()
            : null;

        $topGaps = $assessment
            ? $assessment->items->sortByDesc('gap_size')->take(3)->map(fn ($item) => [
                'name' => $item->skill?->name ?? 'Unknown',
                'gap' => $item->gap_size,
                'priority' => ucfirst($item->priority),
            ])->values()->all()
            : [];

        $fit = [
            'hasSkills' => $hasSkills,
            'matchScore' => $overlap['score'],
            'matchedSkills' => $matchedSkills,
            'missingSkills' => $missingSkills,
            'readiness' => $assessment ? (int) round($assessment->overall_readiness_score) : null,
            'topGaps' => $topGaps,
            'hasAssessment' => (bool) $assessment,
        ];

        return Inertia::render('Careers/Show', [
            'role' => $careerRole,
            'skillsByImportance' => $skillsByImportance,
            'skillCount' => $skillCount,
            'relatedRoles' => $relatedRoles,
            'outgoingPaths' => $outgoingPaths,
            'incomingPaths' => $incomingPaths,
            'fit' => $fit,
        ]);
    }
}