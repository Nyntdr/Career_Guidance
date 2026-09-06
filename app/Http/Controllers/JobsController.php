<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\UserSkill;
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

        foreach ($postings as $posting) {
            $required = $posting->requiredSkills;
            if ($required->isEmpty()) {
                continue;
            }

            $hard = $required->filter(fn ($s) => $s->importance === 'required');
            $soft = $required->filter(fn ($s) => $s->importance === 'preferred');

            $hardMatched = 0;
            $hardTotal = max($hard->count(), 1);
            $softMatched = 0;
            $softTotal = max($soft->count(), 1);
            $matched = [];
            $missing = [];

            foreach ($hard as $req) {
                $skill = $req->skill;
                $user = $userSkillMap->get($req->skill_id);

                if ($user && $user->proficiency_level >= $req->min_proficiency) {
                    $hardMatched++;
                    $matched[] = $skill->name;
                } else {
                    $missing[] = $skill->name;
                }
            }

            foreach ($soft as $req) {
                $skill = $req->skill;
                $user = $userSkillMap->get($req->skill_id);

                if ($user) {
                    $softMatched++;
                    $matched[] = $skill->name;
                }
            }

            $hardRate = $hardMatched / $hardTotal;
            $softRate = $softMatched / $softTotal;

            $score = (int) round($hardRate * 70 + $softRate * 30);

            if ($score > 0) {
                $jobs[] = [
                    'id' => $posting->id,
                    'title' => $posting->title,
                    'company' => $posting->company ?? 'Unknown',
                    'location' => $posting->location,
                    'seniority_level' => $posting->seniority_level,
                    'posted_at' => $posting->posted_at?->toDateString(),
                    'score' => $score,
                    'compatibility' => $score,
                    'matched_skills' => array_values(array_unique($matched)),
                    'missing_skills' => array_values(array_unique($missing)),
                ];
            }
        }

        usort($jobs, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($jobs, 0, 10);
    }
}