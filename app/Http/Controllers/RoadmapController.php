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

        $assessment = GapAssessment::where('profile_id', $profile->id)
            ->with([
                'targetRole',
                'items' => fn ($q) => $q
                    ->with(['skill', 'recommendations.learningResource'])
                    ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
                    ->orderBy('gap_size', 'desc'),
            ])
            ->latest()
            ->first();

        $progress = ProgressTracking::where('profile_id', $profile->id)
            ->whereIn('gap_assessment_item_id', collect($assessment?->items)->pluck('id'))
            ->get()
            ->mapWithKeys(fn ($p) => [$p->gap_assessment_item_id => $p]);

        $items = $assessment
            ? $assessment->items->map(fn ($item) => [
                'id' => $item->id,
                'skill' => $item->skill?->name ?? 'Unknown',
                'required_level' => $item->required_level,
                'current_level' => $item->current_level,
                'gap_size' => $item->gap_size,
                'priority' => ucfirst($item->priority),
                'status' => $progress->get($item->id)?->status ?? 'not_started',
                'progress_id' => $progress->get($item->id)?->id,
                'recommendations' => $item->recommendations->map(fn ($rec) => [
                    'title' => $rec->learningResource?->title ?? 'Unknown resource',
                    'provider' => $rec->learningResource?->provider,
                    'url' => $rec->learningResource?->url,
                    'cost' => $rec->learningResource?->cost,
                    'duration_hours' => $rec->learningResource?->duration_hours,
                ])->values(),
            ])->values()
            : [];

        $roles = CareerRole::query()
            ->withCount('requiredSkills')
            ->orderBy('title')
            ->get()
            ->map(fn ($role) => [
                'id' => $role->id,
                'title' => $role->title,
            ]);

        $counts = collect($progress)
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
            'targetRole' => $assessment?->targetRole?->title ?? null,
        ];

        return Inertia::render('Roadmap/Index', [
            'items' => $items,
            'roles' => $roles,
            'stats' => $stats,
            'hasAssessment' => (bool) $assessment,
            'isStale' => $isStale,
        ]);
    }

    public function updateProgress(Request $request, ProgressTracking $progress): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:not_started,in_progress,completed'],
        ]);

        if ($progress->profile_id !== $request->user()->profile()->firstOrCreate([])->id) {
            abort(403);
        }

        $progress->update(['status' => $validated['status']]);

        return redirect()->back();
    }
}
