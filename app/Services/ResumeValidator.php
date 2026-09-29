<?php

namespace App\Services;

class ResumeValidator
{
    /**
     * Known resume section heading keywords.
     */
    protected array $headingKeywords = [
        'experience' => ['experience', 'work experience', 'employment', 'employment history', 'work history', 'where i worked'],
        'education' => ['education', 'academic background', 'qualification', 'qualifications', 'studies', 'about my studies'],
        'skills' => ['skills', 'technical skills', 'core competencies', 'technologies', 'what i do'],
        'summary' => ['summary', 'professional summary', 'profile', 'about me', 'objective', 'career objective', 'my details'],
        'projects' => ['projects', 'key projects', 'personal projects'],
        'certifications' => ['certifications', 'licenses', 'certificates'],
        'awards' => ['awards', 'honors', 'achievements'],
        'publications' => ['publications'],
        'volunteer' => ['volunteer', 'volunteering', 'community involvement'],
        'languages' => ['languages'],
        'interests' => ['interests', 'hobbies'],
        'contact' => ['contact', 'contact info', 'contact information'],
    ];

    protected array $actionVerbs = [
        'managed', 'led', 'built', 'developed', 'implemented', 'designed',
        'delivered', 'improved', 'reduced', 'increased', 'launched', 'owned',
        'created', 'engineered', 'coordinated', 'spearheaded', 'optimized',
        'architected', 'supervised', 'maintained', 'analyzed', 'automated',
    ];

    /**
     * Assess raw text extracted from a file or pasted by a user.
     *
     * @return array{is_resume: bool, score: int, band: string, signals: string[], reasons: string[]}
     */
    public function assess(string $rawText): array
    {
        $text = trim($rawText);
        $charCount = mb_strlen($text);

        $minChars = (int) config('resume.min_characters', 200);
        $strongThreshold = (int) config('resume.strong_threshold', 6);
        $borderlineThreshold = (int) config('resume.borderline_threshold', 3);

        if ($charCount < $minChars) {
            return [
                'is_resume' => false,
                'score' => 0,
                'band' => 'not_a_resume',
                'signals' => [],
                'reasons' => [
                    "That file doesn't look like a resume — it has less than {$minChars} characters of extracted text.",
                ],
            ];
        }

        $score = 0;
        $signals = [];
        $reasons = [];

        // 1. Section Headings (~2 pts each)
        $lines = explode("\n", $text);
        $matchedHeadings = [];

        foreach ($lines as $line) {
            $lineTrimmed = trim($line);
            if (mb_strlen($lineTrimmed) === 0 || mb_strlen($lineTrimmed) > 60) {
                continue;
            }

            $lineLower = mb_strtolower($lineTrimmed);
            foreach ($this->headingKeywords as $category => $keywords) {
                if (isset($matchedHeadings[$category])) {
                    continue;
                }
                foreach ($keywords as $kw) {
                    if ($lineLower === $kw || str_contains($lineLower, $kw)) {
                        $matchedHeadings[$category] = true;
                        $score += 2;
                        $signals[] = "Section heading: {$category}";
                        break;
                    }
                }
            }
        }

        // 2. Email Address (2 pts)
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text)) {
            $score += 2;
            $signals[] = 'Email address';
        }

        // 3. Phone Number (2 pts)
        if (preg_match('/(?:\+?\d{1,3}[\s.-]?)?\(?\d{2,4}\)?[\s.-]?\d{3,4}[\s.-]?\d{3,4}/', $text)) {
            $score += 2;
            $signals[] = 'Phone number';
        }

        // 4. Date Range (2 pts)
        if (preg_match('/\b(19|20)\d{2}\s*[\x{2013}\x{2014}\-–\/]\s*((19|20)\d{2}|present|current|now)\b/iu', $text) ||
            preg_match('/\b(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+(19|20)\d{2}\b/i', $text)) {
            $score += 2;
            $signals[] = 'Date range';
        }

        // 5. Bullet Markers (1 pt each, cap 3)
        $bulletCount = 0;
        foreach ($lines as $line) {
            $lineTrimmed = trim($line);
            if (preg_match('/^[\x{2022}\x{25CF}\*\-\x{2013}\x{2014}](?:\s+|$)/u', $lineTrimmed)) {
                $bulletCount++;
            }
        }
        $bulletPoints = min($bulletCount, 3);
        if ($bulletPoints > 0) {
            $score += $bulletPoints;
            $signals[] = "Bullet markers ({$bulletPoints})";
        }

        // 6. Action Verbs (1 pt each, cap 2)
        $textLower = mb_strtolower($text);
        $verbCount = 0;
        foreach ($this->actionVerbs as $verb) {
            if (preg_match('/\b' . preg_quote($verb, '/') . '\b/i', $textLower)) {
                $verbCount++;
                if ($verbCount >= 2) {
                    break;
                }
            }
        }
        if ($verbCount > 0) {
            $score += $verbCount;
            $signals[] = "Action verbs ({$verbCount})";
        }

        // 7. Prose Penalty (-2 pts)
        $nonEmptyLines = array_filter(array_map('trim', $lines));
        $avgLineLen = count($nonEmptyLines) > 0 ? (array_sum(array_map('mb_strlen', $nonEmptyLines)) / count($nonEmptyLines)) : 0;
        if ($avgLineLen > 200 && $bulletCount < 3) {
            $score = max(0, $score - 2);
            $signals[] = 'Prose penalty (-2)';
        }

        // Verdict determination
        if ($score >= $strongThreshold) {
            $band = 'strong';
            $isResume = true;
        } elseif ($score >= $borderlineThreshold) {
            $band = 'borderline';
            $isResume = true;
        } else {
            $band = 'not_a_resume';
            $isResume = false;

            $missing = [];
            if (! isset($matchedHeadings['experience'])) {
                $missing[] = 'experience';
            }
            if (! isset($matchedHeadings['education'])) {
                $missing[] = 'education';
            }
            if (! isset($matchedHeadings['skills'])) {
                $missing[] = 'skills';
            }

            $missingStr = count($missing) > 0 ? implode(', ', $missing) : 'key sections';
            $reasons[] = "That file doesn't look like a resume — we couldn't find any {$missingStr} sections. Check you uploaded the right document.";
        }

        return [
            'is_resume' => $isResume,
            'score' => $score,
            'band' => $band,
            'signals' => $signals,
            'reasons' => $reasons,
        ];
    }
}
