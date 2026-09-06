<?php

namespace App\Http\Controllers;

use App\Models\AtsScan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AtsScanController extends Controller
{
    public function show(Request $request, AtsScan $scan): Response
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        abort_unless($scan->resume?->profile_id === $profile->id, 403);

        $scan->load([
            'matchedKeywords',
            'details',
            'resume',
            'jobPosting' => fn ($q) => $q->with('requiredSkills.skill'),
        ]);

        $required = $scan->jobPosting?->requiredSkills
            ->groupBy('importance')
            ->map(fn ($group) => $group->map(fn ($r) => $r->skill?->name))
            ->map(fn ($group) => $group->values());

        return Inertia::render('AtsScans/Show', [
            'scan' => [
                'id' => $scan->id,
                'overall_score' => (int) round($scan->overall_score),
                'keyword_match_score' => (int) round($scan->keyword_match_score),
                'structure_score' => (int) round($scan->structure_score),
                'formatting_score' => (int) round($scan->formatting_score),
                'readability_score' => (int) round($scan->readability_score),
                'created_at' => $scan->created_at?->toDateTimeString(),
                'resume' => [
                    'id' => $scan->resume?->id,
                    'version' => $scan->resume?->version,
                    'uploaded_at' => $scan->resume?->uploaded_at?->toDateTimeString(),
                ],
                'job' => [
                    'id' => $scan->jobPosting?->id,
                    'title' => $scan->jobPosting?->title,
                    'company' => $scan->jobPosting?->company,
                    'required_skills' => $required,
                ],
            ],
            'keywords' => $scan->matchedKeywords->map(fn ($kw) => [
                'keyword' => $kw->keyword,
                'matched' => $kw->matched,
                'frequency_in_resume' => $kw->frequency_in_resume,
                'frequency_in_jd' => $kw->frequency_in_jd,
            ])->values(),
            'details' => $scan->details->map(fn ($d) => [
                'category' => $d->category,
                'detail' => $d->detail,
                'severity' => $d->severity,
                'suggestion' => $d->suggestion,
            ])->values(),
        ]);
    }
}