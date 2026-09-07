<?php

namespace App\Services\Ml;

use App\Support\Settings;
use Throwable;

class ResumeSegmenter
{
    public function __construct(
        protected ModelRepository $repository,
        protected SectionClassifier $classifier,
        protected SectionClusterer $clusterer,
    ) {}

    /**
     * Hybrid section segmentation. Deterministic dictionary headings always
     * win; when a trained model set exists the Random Forest + K-Means path
     * extends detection to fuzzy heading variants. Without models it degrades
     * to the plain dictionary behaviour.
     *
     * @param  array<string, string>  $headings
     * @return list<array{type: string, content: string, method: string}>
     */
    public function segment(string $text, array $headings): array
    {
        $rawLines = preg_split('/\r\n|\r|\n/', $text) ?: [];

        $entries = [];
        $prevIsBlank = true;
        foreach ($rawLines as $raw) {
            $trimmed = trim($raw);
            if ($trimmed === '') {
                $prevIsBlank = true;

                continue;
            }

            $normalized = strtolower((string) preg_replace('/^[\d.\-_)\s]+/', '', $trimmed));

            $entries[] = [
                'trimmed' => $trimmed,
                'normalized' => $normalized,
                'dictionary' => $this->matchHeading($normalized, $headings),
                'prevIsBlank' => $prevIsBlank,
            ];
            $prevIsBlank = false;
        }

        $ml = $this->mlContext($entries);

        $sections = [];
        $current = 'contact';
        $buffer = [];
        $method = 'dictionary';

        $flush = function () use (&$sections, &$current, &$buffer, &$method) {
            $content = trim(implode("\n", $buffer));
            if ($content !== '') {
                $sections[] = [
                    'type' => $current,
                    'content' => $content,
                    'method' => $method,
                ];
            }
            $buffer = [];
        };

        $minConfidence = (float) Settings::get('ml_min_confidence', config('ml.default_confidence', 0.7));

        $anyDictionaryHeading = false;
        foreach ($entries as $entry) {
            if ($entry['dictionary'] !== null) {
                $anyDictionaryHeading = true;
                break;
            }
        }

        foreach ($entries as $index => $entry) {
            if ($entry['dictionary'] !== null) {
                $flush();
                $current = $entry['dictionary'];
                $method = 'dictionary';

                continue;
            }

            $mlEntry = $ml[$index] ?? null;
            $label = $mlEntry['label'] ?? null;
            $score = (float) ($mlEntry['score'] ?? 0.0);

            if ($label !== null
                && $label !== $current
                && $label !== 'contact'
                && $buffer !== []
                && $entry['prevIsBlank']
                && ($anyDictionaryHeading ? $current === 'contact' : true)
                && $this->isHeadingLike($entry['trimmed'], $score, $minConfidence)
            ) {
                $flush();
                $current = $label;
                $method = 'ml';

                continue;
            }

            $buffer[] = $entry['trimmed'];
        }

        $flush();

        return $sections;
    }

    /**
     * Predict labels for every non-dictionary line and smooth low-confidence
     * predictions through the K-Means neighbourhood majority.
     *
     * @param  list<array{trimmed: string, normalized: string, dictionary: ?string}>  $entries
     * @return array<int, array{label: string, score: float}>
     */
    protected function mlContext(array $entries): array
    {
        if ((string) Settings::get('ml_enabled', 'enabled') === 'disabled') {
            return [];
        }

        if (! $this->repository->isReady()) {
            return [];
        }

        try {
            $vectorizer = new ResumeLineVectorizer($this->repository->vocab());
            $indexes = [];
            $vectors = [];

            foreach ($entries as $index => $entry) {
                if ($entry['dictionary'] !== null) {
                    continue;
                }

                $indexes[] = $index;
                $vectors[] = $vectorizer->vectorize($entry['trimmed']);
            }

            if ($indexes === []) {
                return [];
            }

            $predictions = $this->classifier->predictMany($vectors);
            $clusters = $this->clusterer->clusterMany($vectors);

            if ($predictions === null) {
                return [];
            }

            $result = [];
            foreach ($predictions as $i => $prediction) {
                $result[$indexes[$i]] = [
                    'label' => $prediction['label'],
                    'score' => $prediction['score'],
                    'cluster' => $clusters[$i] ?? null,
                ];
            }

            $this->smoothByCluster($result);

            return $result;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @param  array<int, array{label: string, score: float, cluster: ?int}>  $lines
     */
    protected function smoothByCluster(array &$lines): void
    {
        if ($lines === []) {
            return;
        }

        $minConfidence = (float) Settings::get('ml_min_confidence', config('ml.default_confidence', 0.7));
        $minPurity = (float) Settings::get('ml_cluster_purity', config('ml.cluster_purity', 0.7));

        $grouped = [];
        foreach ($lines as $index => $line) {
            $grouped[$line['cluster']][] = $index;
        }

        $minClusterSize = (int) config('ml.min_cluster_size', 2);

        $majority = [];
        foreach ($grouped as $cluster => $members) {
            if (count($members) < $minClusterSize) {
                continue;
            }

            $labels = [];
            foreach ($members as $member) {
                $labels[$lines[$member]['label']] = ($labels[$lines[$member]['label']] ?? 0) + 1;
            }

            arsort($labels);
            $dominant = (string) array_key_first($labels);
            $purity = max($labels) / count($members);
            $majority[$cluster] = ['label' => $dominant, 'purity' => $purity];
        }

        foreach ($lines as $index => &$line) {
            if ($line['score'] >= $minConfidence || ! isset($majority[$line['cluster']])) {
                continue;
            }

            $region = $majority[$line['cluster']];
            if ($region['purity'] < $minPurity) {
                continue;
            }

            $line['label'] = $region['label'];
            $line['score'] = $region['purity'];
        }
        unset($line);
    }

    protected function matchHeading(string $normalized, array $headings): ?string
    {
        if (strlen($normalized) > 60) {
            return null;
        }

        return $headings[$normalized] ?? $headings[rtrim($normalized, ": \t")] ?? null;
    }

    protected function isHeadingLike(string $line, float $score, float $minConfidence): bool
    {
        $trimmed = trim($line);

        if ($trimmed === ''
            || mb_strlen($trimmed) > 100
            || $score < $minConfidence
            || $this->looksLikeContent($trimmed)
        ) {
            return false;
        }

        $words = preg_split('/\s+/', $trimmed) ?: [];
        $words = array_values(array_filter($words, fn ($w) => $w !== ''));

        if ($words === [] || count($words) > 6) {
            return false;
        }

        $capitalized = 0;
        $allCaps = true;
        foreach ($words as $word) {
            if (preg_match('/^\p{Lu}/u', $word) === 1) {
                $capitalized++;
            }
            if (preg_match('/^[\p{Lu}\p{N}\-&.\'()\/]+$/u', $word) !== 1) {
                $allCaps = false;
            }
        }

        $endsInPeriod = str_ends_with($trimmed, '.');

        if (count($words) === 1) {
            return ($allCaps || str_ends_with($trimmed, ':')) && ! $endsInPeriod;
        }

        $titleCase = $capitalized / count($words) >= 0.6;

        return ($titleCase || $allCaps || str_ends_with($trimmed, ':')) && ! $endsInPeriod;
    }

    /**
     * Structural negatives: bullet/list lines, date/year ranges and
     * consultant attribution lines are never headings.
     */
    protected function looksLikeContent(string $line): bool
    {
        if (preg_match('/^[\x{2022}\x{25CF}\x{25E6}\x{25AA}\x{2023}\x{2219}\x{2013}\x{2014}*]\s?/u', $line) === 1) {
            return true;
        }

        if (preg_match('/\d{4}/', $line) === 1) {
            return true;
        }

        if (preg_match('/^(client|employer|current company|project name|role)\s*:/i', $line) === 1) {
            return true;
        }

        $normalized = strtolower((string) preg_replace('/^[\d.\-_)\s]+/', '', $line));

        if (preg_match('/^(responsibilities|roles?\s*(and|&)\s*responsibilities|key responsibilities|duties|job\s*(responsibilities|description|duties)|project\s*(description|overview))/', rtrim($normalized, ": \t")) === 1) {
            return true;
        }

        return false;
    }
}
