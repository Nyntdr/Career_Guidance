<?php

namespace App\Http\Controllers;

use App\Models\CareerRole;
use App\Models\GapAssessment;
use App\Models\ProgressTracking;
use App\Models\UserSkill;
use App\Services\SkillGapAnalyzer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoadmapController extends Controller
{
    public function index(Request $request): Response
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $roleId = $request->query('role');
        $selectedRole = null;

        if ($roleId !== null && $roleId !== '') {
            $selectedRole = CareerRole::findOrFail($roleId);
        }

        $allAssessments = GapAssessment::where('profile_id', $profile->id)
            ->with('targetRole:id,title')
            ->withCount('items')
            ->latest()
            ->get();

        $assessment = null;
        if ($selectedRole) {
            $assessment = $allAssessments->firstWhere('target_role_id', $selectedRole->id);
        } else {
            $assessment = $allAssessments->first();
            $selectedRole = $assessment?->targetRole;
        }

        $items = [];
        $progressMap = collect();

        if ($assessment) {
            $assessment->load([
                'items' => fn ($q) => $q
                    ->with(['skill', 'recommendations.learningResource'])
                    ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
                    ->orderBy('gap_size', 'desc'),
            ]);

            $progressMap = ProgressTracking::where('profile_id', $profile->id)
                ->whereIn('gap_assessment_item_id', $assessment->items->pluck('id'))
                ->get()
                ->mapWithKeys(fn ($p) => [$p->gap_assessment_item_id => $p]);

            $items = $assessment->items->map(fn ($item) => [
                'id' => $item->id,
                'skill' => $item->skill?->name ?? 'Unknown',
                'required_level' => $item->required_level,
                'current_level' => $item->current_level,
                'gap_size' => $item->gap_size,
                'priority' => ucfirst($item->priority),
                'status' => $progressMap->get($item->id)?->status ?? 'not_started',
                'progress_id' => $progressMap->get($item->id)?->id,
                'recommendations' => $item->recommendations->map(fn ($rec) => [
                    'title' => $rec->learningResource?->title ?? 'Unknown resource',
                    'provider' => $rec->learningResource?->provider,
                    'url' => $rec->learningResource?->url,
                    'cost' => $rec->learningResource?->cost,
                    'duration_hours' => $rec->learningResource?->duration_hours,
                ])->values(),
            ])->values();
        }

        $roles = CareerRole::query()
            ->withCount('requiredSkills')
            ->orderBy('title')
            ->get()
            ->map(fn ($role) => [
                'id' => $role->id,
                'title' => $role->title,
            ]);

        $counts = $progressMap
            ->map(fn ($p) => $p->status)
            ->countBy();

        $isStale = false;

        if ($assessment && $assessment->skills_fingerprint) {
            $userSkillMap = UserSkill::where('profile_id', $profile->id)
                ->get()
                ->keyBy('skill_id');

            $isStale = $assessment->skills_fingerprint !== SkillGapAnalyzer::fingerprint($userSkillMap);
        }

        $stats = [
            'readiness' => $assessment ? (int) round($assessment->overall_readiness_score) : null,
            'gapCount' => count($items),
            'inProgress' => $counts->get('in_progress', 0),
            'completed' => $counts->get('completed', 0),
            'targetRole' => $selectedRole?->title ?? $assessment?->targetRole?->title ?? null,
            'targetRoleId' => $selectedRole?->id ?? $assessment?->target_role_id ?? null,
        ];

        $assessmentsList = $allAssessments->map(fn ($a) => [
            'id' => $a->id,
            'target_role_id' => $a->target_role_id,
            'role_title' => $a->targetRole?->title ?? 'Unknown Role',
            'readiness' => (int) round($a->overall_readiness_score),
            'created_at' => $a->created_at?->toISOString(),
            'item_count' => $a->items_count,
        ])->values();

        return Inertia::render('Roadmap/Index', [
            'items' => $items,
            'roles' => $roles,
            'stats' => $stats,
            'assessments' => $assessmentsList,
            'selectedAssessmentId' => $assessment?->id ?? null,
            'selectedRoleId' => $selectedRole?->id ?? null,
            'hasAssessment' => (bool) $assessment,
            'isStale' => $isStale,
        ]);
    }

    public function updateProgress(\App\Http\Requests\RoadmapProgressUpdateRequest $request, ProgressTracking $progress): RedirectResponse
    {
        $validated = $request->validated();

        if ($progress->profile_id !== $request->user()->profile()->firstOrCreate([])->id) {
            abort(403);
        }

        $progress->update(['status' => $validated['status']]);

        return redirect()->back();
    }
}
