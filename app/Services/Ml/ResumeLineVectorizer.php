<?php

namespace App\Services\Ml;

class ResumeLineVectorizer
{
    /**
     * Strong heading-indicator words always kept in the vocabulary so a
     * freshly trained model never loses them, even on a small corpus.
     */
    public const BASE_VOCAB = [
        'summary', 'objective', 'profile', 'professional', 'experience', 'work',
        'employment', 'history', 'skills', 'skill', 'competencies', 'competency',
        'proficiencies', 'proficiency', 'languages', 'education', 'degree',
        'certifications', 'certification', 'licenses', 'certificates', 'projects',
        'personal', 'publications', 'awards', 'honors', 'volunteer', 'interests',
        'references', 'background', 'contact', 'career', 'technical', 'core',
        'relevant', 'achievements', 'training', 'academic', 'tools', 'technologies',
        'programming', 'database', 'coursework', 'internship', 'additional',
        'university', 'institution', 'school', 'research', 'engineering',
        'background', 'overview', 'development', 'additional', 'information',
        'qualifications', 'expertise', 'strengths', 'strongholds',
        'accomplishments', 'highlights', 'record', 'areas', 'papers',
        'presentations', 'talks', 'workshops', 'seminars', 'spoken',
        'proficiency', 'hobbies', 'extracurricular', 'service', 'academic',
        'thesis', 'coursework', 'certified', 'credentials', 'memberships',
    ];

    public function __construct(protected array $vocab = [])
    {
        if ($vocab === []) {
            $vocab = self::BASE_VOCAB;
        }

        $this->vocab = array_values(array_unique($vocab));
    }

    /**
     * @return list<float>
     */
    public function vectorize(string $line): array
    {
        $line = trim($line);
        $lower = strtolower($line);
        $bag = $this->words($lower);

        $vector = [];
        foreach ($this->vocab as $feature) {
            $vector[] = $this->termFrequency($bag, $feature, $lower);
        }

        $rawWords = preg_split('/\s+/', $line) ?: [];
        $rawWords = array_values(array_filter($rawWords, fn ($w) => $w !== ''));
        $totalWords = max(count($rawWords), 1);

        $capitalized = count(array_filter($rawWords, fn ($w) => preg_match('/^\p{Lu}/u', $w) === 1));
        $titleRatio = $capitalized / $totalWords;

        $allCaps = 0.0;
        if (count($rawWords) > 0) {
            $every = array_filter($rawWords, fn ($w) => preg_match('/^[\p{Lu}\p{N}\-&.\'()\/]+$/u', $w) === 1);
            $allCaps = count($every) === count($rawWords) ? 1.0 : 0.0;
        }

        $digitCount = min(3, preg_match_all('/\d/', $line) ?: 0);

        $avgWordLen = 0.0;
        if ($totalWords > 0) {
            $avgWordLen = array_sum(array_map('strlen', $rawWords)) / $totalWords;
        }

        $vector[] = min(1.0, $totalWords / 8);
        $vector[] = $titleRatio;
        $vector[] = $allCaps;
        $vector[] = $digitCount / 3;
        $vector[] = str_ends_with($line, ':') ? 1.0 : 0.0;
        $vector[] = str_ends_with($line, '.') ? 1.0 : 0.0;
        $vector[] = min(1.0, $avgWordLen / 10);
        $vector[] = min(1.0, mb_strlen($line) / 80);

        return $vector;
    }

    /**
     * Build a stable vocabulary from a corpus of lines: single words plus
     * frequent words pairs/triples, which capture multi-word headings such
     * as "work experience" and "areas of expertise".
     *
     * BASE_VOCAB is always retained, so the remaining budget is spent on the
     * most frequent learned terms. Ranking must be by frequency: sorting the
     * terms alphabetically and then truncating keeps whichever terms happen to
     * sort first, which on a large corpus silently drops every useful term
     * after "c".
     */
    public static function buildVocabulary(iterable $lines, ?int $cap = null): array
    {
        $cap ??= (int) config('ml.vocab_cap', 220);
        $minCount = (int) config('ml.vocab_min_count', 2);

        $counts = [];

        foreach ($lines as $line) {
            $words = self::wordsFrom((string) $line);

            foreach ($words as $word) {
                $counts[$word] = ($counts[$word] ?? 0) + 1;
            }

            for ($i = 0; $i < count($words) - 1; $i++) {
                $phrase = $words[$i].' '.$words[$i + 1];
                $counts[$phrase] = ($counts[$phrase] ?? 0) + 1;
            }

            for ($i = 0; $i < count($words) - 2; $i++) {
                $phrase = $words[$i].' '.$words[$i + 1].' '.$words[$i + 2];
                $counts[$phrase] = ($counts[$phrase] ?? 0) + 1;
            }
        }

        // Most frequent first; ties broken alphabetically so the vocabulary is
        // reproducible across runs.
        $counts = array_filter($counts, fn ($c) => $c >= $minCount);
        uksort($counts, function ($a, $b) use ($counts) {
            return $counts[$b] <=> $counts[$a] ?: strcmp((string) $a, (string) $b);
        });

        $base = array_values(array_unique(self::BASE_VOCAB));
        $budget = max($cap - count($base), 0);

        $learned = [];
        foreach (array_keys($counts) as $term) {
            if (count($learned) >= $budget) {
                break;
            }
            $learned[] = $term;
        }

        return array_values(array_unique(array_merge($base, $learned)));
    }

    /**
     * @return list<string>
     */
    public function words(string $lowerLine): array
    {
        return self::wordsFrom($lowerLine);
    }

    /**
     * @return list<string>
     */
    protected static function wordsFrom(string $line): array
    {
        $parts = preg_split('/[^\p{L}\p{N}_\-]+/u', strtolower($line)) ?: [];
        $parts = array_map(trim(...), $parts);

        return array_values(array_filter($parts, function ($word) {
            return $word !== '' && preg_match('/^[\d.]+$/', $word) !== 1;
        }));
    }

    protected function termFrequency(array $words, string $feature, string $lowerLine): float
    {
        // Phrase features (contain a space) are matched against the raw
        // lowercased line; single words are counted from the token list.
        if (str_contains($feature, ' ')) {
            preg_match_all('/\b'.preg_quote($feature, '/').'\b/', $lowerLine, $matches);

            return min(1.0, count($matches[0]) / 3);
        }

        $count = 0;
        foreach ($words as $word) {
            if ($word === $feature) {
                $count++;
            }
        }

        return min(1.0, $count / 3);
    }
}
