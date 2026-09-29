<?php

namespace App\Http\Controllers;

use App\Models\CareerRecommendation;
use App\Services\RecommendationEngine;
use App\Services\RecommendationRationalizer;
use App\Services\RoleMatchScorer;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationController extends Controller
{
    public function index(Request $request): Response
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $userSkillMap = $profile->userSkills()->with('skill')->get()->keyBy('skill_id');

        $recommendations = CareerRecommendation::where('profile_id', $profile->id)
            ->with('careerRole.requiredSkills')
            ->orderByDesc('match_score')
            ->get();

        if ($recommendations->isEmpty() && $userSkillMap->isNotEmpty()) {
            $lock = Cache::lock('rec-generate:'.$profile->id, 120);

            if ($lock->get()) {
                try {
                    app(RecommendationEngine::class)->generate($profile);
                } finally {
                    $lock->release();
                }
            }

            $recommendations = CareerRecommendation::where('profile_id', $profile->id)
                ->with('careerRole.requiredSkills')
                ->orderByDesc('match_score')
                ->get();
        }

        $scorer = app(RoleMatchScorer::class);

        $items = $recommendations->map(function ($rec) use ($scorer, $userSkillMap) {
            $role = $rec->careerRole;

            if (! $role) {
                return null;
            }

            $overlap = $scorer->overlap($userSkillMap, $role);
            $nameById = $role->requiredSkills->keyBy('id');

            return [
                'id' => $rec->id,
                'role_id' => $role->id,
                'title' => $role->title,
                'category' => $role->typical_seniority ?? 'General',
                'salary_range' => $this->formatSalary($role->avg_salary_range),
                'growth_outlook' => $role->growth_outlook ?? '',
                'match_score' => (int) round($rec->match_score),
                'rationale' => $rec->rationale ?? '',
                'rationale_source' => $rec->rationale_source ?? 'template',
                'generated_at' => $rec->generated_at?->toDateTimeString(),
                'matched_skills' => collect($overlap['matched'])
                    ->map(fn ($id) => (string) $nameById->get($id)?->name)
                    ->filter()
                    ->values()
                    ->all(),
                'missing_skills' => collect($overlap['missing'])
                    ->map(fn ($id) => (string) $nameById->get($id)?->name)
                    ->filter()
                    ->values()
                    ->all(),
            ];
        })->filter()->values();

        $assessedRoleIds = \App\Models\GapAssessment::where('profile_id', $profile->id)
            ->pluck('target_role_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $hasAssessmentMap = array_fill_keys($assessedRoleIds, true);

        return Inertia::render('Recommendations/Index', [
            'recommendations' => $items,
            'hasAssessmentMap' => $hasAssessmentMap,
            'llmEnabled' => app(RecommendationRationalizer::class)->isEnabled(),
            'stats' => [
                'skillsAssessed' => $userSkillMap->count(),
                'generatedAt' => $recommendations->first()?->generated_at?->toDateTimeString(),
                'recommendations' => $items->count(),
                'topMatch' => $first['match_score'] ?? 0,
            ],
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $count = app(RecommendationEngine::class)->generate($profile)->count();

        return redirect()->back()->with(
            'success',
            $count > 0
                ? 'Generated '.$count.' personalized career recommendation'.($count === 1 ? '' : 's').'.'
                : 'Add skills to your profile to generate recommendations.'
        );
    }

    public function rationale(Request $request, CareerRecommendation $recommendation): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        abort_unless($recommendation->profile_id === $profile->id, 403);

        $role = $recommendation->careerRole;

        abort_unless($role, 404);

        $userSkillMap = $profile->userSkills()->with('skill')->get()->keyBy('skill_id');
        $overlap = app(RoleMatchScorer::class)->overlap($userSkillMap, $role);

        $rationalizer = app(RecommendationRationalizer::class);

        $lock = Cache::lock('rationalize:'.$profile->id, 10);

        $lockAcquired = (bool) $lock->get();
        $llmEnabled = false;

        if ($lockAcquired) {
            try {
                $llmEnabled = $rationalizer->isEnabled();
                $result = $rationalizer->for($profile, $role, $overlap);
            } finally {
                $lock->release();
            }
        } else {
            $result = [
                'text' => $rationalizer->template($profile, $role, $overlap),
                'source' => 'template',
            ];
        }

        $recommendation->update([
            'rationale' => $result['text'],
            'rationale_source' => $result['source'],
        ]);

        [$flashKey, $flashMessage] = match (true) {
            $result['source'] === 'llm' => [
                'success',
                'AI explanation generated for '.$role->title.'.',
            ],
            // Lock was held by a concurrent request; no provider call was made.
            ! $lockAcquired => [
                'info',
                'An AI explanation for '.$role->title.' is already being generated — try again in a moment.',
            ],
            // Enabled and configured, yet we still fell back: the provider failed.
            $llmEnabled => [
                'warning',
                'The AI provider is unavailable right now — showing the standard explanation. Try again shortly.',
            ],
            default => [
                'info',
                'AI explanations are turned off. Enable them under Admin → Settings → ML, or set a provider key.',
            ],
        };

        return redirect()->back()->with($flashKey, $flashMessage);
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
