<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\Skill;
use App\Models\UserSkill;
use App\Services\RoleMatchScorer;
use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobsController extends Controller
{
    public function index(Request $request): Response
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $userSkills = UserSkill::where('profile_id', $profile->id)
            ->with('skill')
            ->get();

        $userSkillMap = $userSkills->keyBy('skill_id');

        $postings = JobPosting::with('requiredSkills.skill')
            ->orderByDesc('posted_at')
            ->get();

        $jobs = $this->scoreJobs($postings, $userSkillMap);

        $stats = [
            'skillsAssessed' => $userSkills->count(),
            'totalJobs' => $postings->count(),
            'matchedJobs' => count($jobs),
            'topScore' => $jobs[0]['score'] ?? 0,
        ];

        return Inertia::render('Jobs/Index', [
            'jobs' => $jobs,
            'stats' => $stats,
        ]);
    }

    private function scoreJobs($postings, $userSkillMap): array
    {
        $jobs = [];
        $scorer = app(RoleMatchScorer::class);
        $skillsById = Skill::with('aliases')->get()->keyBy('id');

        foreach ($postings as $posting) {
            $required = $posting->requiredSkills;
            if ($required->isEmpty()) {
                continue;
            }

            $result = $scorer->score($userSkillMap, $required);
            $score = $result['score'];

            if ($score > 0) {
                $matched = [];
                $missing = [];

                foreach ($result['matched'] as $skillId) {
                    $matched[] = $skillsById[$skillId]->name ?? $skillId;
                }
                foreach ($result['missing'] as $skillId) {
                    $missing[] = $skillsById[$skillId]->name ?? $skillId;
                }

                $jobs[] = [
                    'id' => $posting->id,
                    'title' => $posting->title,
                    'company' => $posting->company ?? 'Unknown',
                    'location' => $posting->location,
                    'seniority_level' => $posting->seniority_level,
                    'posted_at' => $posting->posted_at?->toDateString(),
                    'score' => $score,
                    'compatibility' => $score,
                    'matched_skills' => $matched,
                    'missing_skills' => $missing,
                ];
            }
        }

        usort($jobs, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($jobs, 0, Settings::get('top_matches', 10));
    }
}
