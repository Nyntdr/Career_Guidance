<?php

namespace App\Services;

use App\Models\AtsMatchedKeyword;
use App\Models\AtsScan;
use App\Models\AtsScanDetail;
use App\Models\JobPosting;
use App\Models\Resume;
use App\Support\Settings;

class AtsScanner
{
    private array $actionVerbs = [
        'led', 'built', 'created', 'developed', 'managed', 'designed',
        'implemented', 'improved', 'increased', 'reduced', 'delivered',
        'launched', 'coordinated', 'researched', 'analyzed', 'drove',
        'architected', 'optimized', 'automated', 'spearheaded',
    ];

    public function scan(Resume $resume, JobPosting $jobPosting): AtsScan
    {
        $resumeText = $resume->raw_text ?? '';
        $jdText = $jobPosting->raw_description ?? '';

        $resume->loadMissing('sections', 'profile');

        [$keywordScore, $keywords] = $this->keywordScore($resumeText, $jdText, $jobPosting);
        $structureScore = $this->structureScore($resume);
        $formattingScore = $this->formattingScore($resumeText);
        $grade = app(Readability::class)->gradeLevel($resumeText);
        $readabilityScore = $this->readabilityScore($grade);
        $weights = $this->weights();

        $overallScore = round(
            $keywordScore * $weights['keyword'] / 100
                + $structureScore * $weights['structure'] / 100
                + $formattingScore * $weights['formatting'] / 100
                + $readabilityScore * $weights['readability'] / 100,
        );

        $scan = AtsScan::create([
            'resume_id' => $resume->id,
            'job_posting_id' => $jobPosting->id,
            'overall_score' => $overallScore,
            'keyword_match_score' => $keywordScore,
            'formatting_score' => $formattingScore,
            'structure_score' => $structureScore,
            'readability_score' => $readabilityScore,
        ]);

        foreach ($keywords as $keyword) {
            AtsMatchedKeyword::create([
                'ats_scan_id' => $scan->id,
                'keyword' => $keyword['keyword'],
                'matched' => $keyword['matched'],
                'frequency_in_resume' => $keyword['frequency_in_resume'],
                'frequency_in_jd' => $keyword['frequency_in_jd'],
            ]);
        }

        $this->writeDetails($scan, $keywords, $resume, $grade);

        return $scan->load(['matchedKeywords', 'details']);
    }

    private function keywordScore(string $resumeText, string $jdText, JobPosting $jobPosting): array
    {
        $requirements = $jobPosting->requiredSkills()->with('skill.aliases')->get();
        $keywords = [];
        $hardMatched = 0;
        $hardTotal = 0;
        $softMatched = 0;
        $softTotal = 0;

        foreach ($requirements as $req) {
            $skill = $req->skill;
            if (!$skill) {
                continue;
            }

            $needle = strtolower($skill->name);
            $freqResume = $this->countOccurrences($resumeText, $needle);
            $matched = $freqResume > 0;

            foreach ($skill->aliases as $alias) {
                $aliasFreq = $this->countOccurrences($resumeText, strtolower($alias->alias));
                $freqResume += $aliasFreq;
                $matched = $matched || $aliasFreq > 0;
            }

            $freqJd = $this->countOccurrences($jdText, $needle);

            if ($req->importance === 'required') {
                $hardTotal++;
                if ($matched) {
                    $hardMatched++;
                }
            } else {
                $softTotal++;
                if ($matched) {
                    $softMatched++;
                }
            }

            $keywords[] = [
                'keyword' => $skill->name,
                'matched' => $matched,
                'frequency_in_resume' => $freqResume,
                'frequency_in_jd' => $freqJd,
            ];
        }

        $score = round(
            ($hardTotal > 0 ? ($hardMatched / $hardTotal) * 70 : 0)
            + ($softTotal > 0 ? ($softMatched / $softTotal) * 30 : 0),
        );

        return [$score, $keywords];
    }

    private function structureScore(Resume $resume): int
    {
        $expected = ['summary', 'experience', 'education', 'skills'];
        $present = $resume->sections->pluck('section_type')->unique();

        $found = collect($expected)->filter(fn ($type) => $present->contains($type))->count();

        return (int) round(($found / count($expected)) * 100);
    }

    private function formattingScore(string $resumeText): int
    {
        $lines = preg_split('/\r\n|\r|\n/', $resumeText) ?: [];

        $bullets = count(array_filter($lines, fn ($line) => preg_match('/^\s*[-•*▪]\s+/', $line)));

        $lower = strtolower($resumeText);
        $verbHits = 0;
        foreach ($this->actionVerbs as $verb) {
            $verbHits += substr_count($lower, $verb);
        }

        preg_match_all('/[$%]|\b\d{2,4}\b/', $resumeText, $matches);
        $quantified = count($matches[0]);

        return (int) round((min(1, $bullets / 8) * 40) + (min(1, $verbHits / 5) * 30) + (min(1, $quantified / 3) * 30));
    }

    private function readabilityScore(float $grade): int
    {
        if ($grade === 0) {
            return 50;
        }

        return (int) round(max(20, min(95, 100 - max(0, $grade - 7) * 5)));
    }

    private function weights(): array
    {
        $default = [
            'keyword' => 50,
            'structure' => 15,
            'formatting' => 15,
            'readability' => 20,
        ];

        $weights = Settings::get('ats_weights', $default);

        $weights = is_array($weights)
            ? array_merge($default, $weights)
            : $default;

        $total = array_sum($weights);

        if ($total !== 100) {
            $weights = array_map(fn ($w) => (int) round($w / max($total, 1) * 100), $weights);
        }

        return $weights;
    }

    private function countOccurrences(string $haystack, string $needle): int
    {
        if ($needle === '') {
            return 0;
        }

        preg_match_all('/\b' . preg_quote($needle, '/') . '\b/i', $haystack, $matches);

        return count($matches[0]);
    }

    private function writeDetails(AtsScan $scan, array $keywords, Resume $resume, float $grade): void
    {
        foreach ($keywords as $keyword) {
            if ($keyword['matched']) {
                continue;
            }

            AtsScanDetail::create([
                'ats_scan_id' => $scan->id,
                'category' => 'keyword',
                'detail' => 'Missing keyword: ' . $keyword['keyword'],
                'severity' => 'medium',
                'suggestion' => 'Mention "' . $keyword['keyword'] . '" specifically using the phrasing from the job description.',
            ]);
        }

        $ok = $resume->sections->pluck('section_type')->toArray();
        $structural = [
            ['summary', 'A summary/objective section', 'Add a short professional summary at the top.'],
            ['experience', 'A work experience section with bullet points', 'Add a work experience section describing your roles and impact.'],
            ['education', 'An education section', 'Add an education section with your degree and institution.'],
            ['skills', 'A dedicated skills section', 'Add a skills section listing your core competencies.'],
        ];

        foreach ($structural as [$type, $label, $suggestion]) {
            if (in_array($type, $ok, true)) {
                continue;
            }

            AtsScanDetail::create([
                'ats_scan_id' => $scan->id,
                'category' => 'structure',
                'detail' => 'Missing ' . $label . '.',
                'severity' => 'high',
                'suggestion' => $suggestion,
            ]);
        }

        if ($grade > 12) {
            AtsScanDetail::create([
                'ats_scan_id' => $scan->id,
                'category' => 'readability',
                'detail' => 'Resume reads at grade ' . round($grade, 1) . ', which is hard to parse quickly.',
                'severity' => 'low',
                'suggestion' => 'Use shorter sentences and simpler wording where possible.',
            ]);
        }
    }
}