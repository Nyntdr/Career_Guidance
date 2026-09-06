<?php

namespace App\Services;

class Readability
{
    public function gradeLevel(string $text): float
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $wordCount = count($words);

        if ($wordCount < 10) {
            return 0;
        }

        $sentences = $this->sentenceCount($text);
        $syllables = array_sum(array_map(fn ($word) => $this->syllableCount($word), $words));

        if ($sentences === 0 || $wordCount === 0) {
            return 0;
        }

        // Flesch-Kincaid Grade Level
        $score = 0.39 * ($wordCount / $sentences) + 11.8 * ($syllables / $wordCount) - 15.59;

        return max(0, round($score, 1));
    }

    public function score(string $text): int
    {
        $grade = $this->gradeLevel($text);

        if ($grade === 0) {
            return 50;
        }

        // Easier to read = better for recruiters. ~90 at grade 5, tapering off.
        return (int) round(max(20, min(95, 100 - max(0, $grade - 5) * 7)));
    }

    private function sentenceCount(string $text): int
    {
        preg_match_all('/[.!?]+(\s|$)/', $text, $matches);

        return count($matches[0]) ?: 1;
    }

    private function syllableCount(string $word): int
    {
        $word = strtolower(preg_replace('/[^a-z]/', '', $word));
        if ($word === '') {
            return 0;
        }

        $count = preg_match_all('/[aeiouy]+/', $word);

        if (preg_match('/e$/', $word)) {
            $count--;
        }

        return max(1, $count);
    }
}