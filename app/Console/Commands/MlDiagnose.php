<?php

namespace App\Console\Commands;

use App\Services\Ml\ModelRepository;
use App\Services\Ml\ResumeLineVectorizer;
use App\Services\Ml\ResumeSegmenter;
use App\Services\Ml\SectionClassifier;
use App\Services\ResumeParser;
use App\Support\Settings;
use Illuminate\Console\Command;

class MlDiagnose extends Command
{
    protected $signature = 'ml:diagnose {--dir=tests/Fixtures/resumes} {--limit=0}';

    protected $description = 'Diagnose ML vs dictionary segmentation across fixture resumes';

    public function handle(): int
    {
        $dir = base_path($this->option('dir'));
        $limit = (int) $this->option('limit');

        $files = collect(glob($dir.'/*.txt'))->sort();
        if ($limit > 0) {
            $files = $files->take($limit);
        }

        Settings::flush();
        $parser = app(ResumeParser::class);
        $segmenter = app(ResumeSegmenter::class);

        $dirChanged = 0;
        $proposals = [];
        $dictionary = $this->dictionaryHeadings();

        $classifier = app(SectionClassifier::class);
        $repository = app(ModelRepository::class);
        $vectorizer = new ResumeLineVectorizer($repository->vocab());
        $useMl = $repository->isReady();

        foreach ($files as $file) {
            $text = (string) file_get_contents($file);

            Settings::save(['ml_enabled' => 'disabled']);
            $dict = $parser->segmentSections($text);

            Settings::save(['ml_enabled' => 'enabled']);
            $ml = $parser->segmentSections($text);

            $dictTypes = array_column($dict, 'type');
            $mlTypes = array_column($ml, 'type');

            if ($mlTypes !== $dictTypes) {
                $dirChanged++;
                $this->line(sprintf(
                    'CHANGED  %-55s dict=[%s] ml=[%s]',
                    basename($file),
                    implode(',', $dictTypes),
                    implode(',', $mlTypes),
                ));
            }

            foreach ($ml as $section) {
                if (($section['method'] ?? 'dictionary') === 'ml') {
                    $anchor = $this->anchorLine($text, $section);

                    $this->line(sprintf(
                        '    ml-section: %-14s heading=%s starts: %s',
                        $section['type'],
                        $anchor,
                        mb_substr(trim($section['content']) ?: '', 0, 30, 'UTF-8'),
                    ));
                }
            }

            if (! $useMl) {
                continue;
            }

            $entries = [];
            foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
                $t = trim($line);
                if ($t !== '') {
                    $entries[] = ['trimmed' => $t, 'normalized' => strtolower((string) preg_replace('/^[\d.\-_)\s]+/', '', $t))];
                }
            }

            $headingLike = [];
            foreach ($entries as $entry) {
                if ($dictionary[$entry['normalized']] ?? null) {
                    continue;
                }
                [$label, $score] = $this->propose($vectorizer, $classifier, $entry['trimmed']);
                if ($label !== null && $label !== 'contact') {
                    $headingLike[] = [$label, $score, $entry['trimmed']];
                }
            }

            if ($headingLike !== []) {
                $this->line(sprintf('  PROPOSALS  %s', basename($file)));
                foreach ($headingLike as [$label, $score, $line]) {
                    $proposals[] = [$label, $score, $line];
                }
            }
        }

        $this->newLine();
        $this->info(sprintf('Resumes scanned: %d | segmented differently: %d | models ready: %s', $files->count(), $dirChanged, $useMl ? 'yes' : 'no'));

        $this->newLine();
        $this->info('Heading-shaped RF proposals (label | score | line):');
        $seen = [];
        foreach ($proposals as [$label, $score, $line]) {
            $key = $label.'|'.$line;
            if (isset($seen[$key]) && count($seen) > 60) {
                continue;
            }
            $seen[$key] = true;
            $this->line(sprintf('  %-14s | %0.2f | %s', $label, $score, mb_substr($line, 0, 70)));
        }

        return self::SUCCESS;
    }

    private function anchorLine(string $text, array $section): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $firstContent = explode("\n", trim($section['content']) ?: '')[0] ?? '';

        foreach ($lines as $i => $line) {
            if (trim($line) === $firstContent && $firstContent !== '') {
                $anchor = $lines[$i - 1] ?? '';
                if (trim((string) $anchor) !== '') {
                    return mb_substr(trim((string) $anchor), 0, 40, 'UTF-8');
                }

                for ($j = $i - 2; $j >= 0; $j--) {
                    if (trim((string) ($lines[$j] ?? '')) !== '') {
                        return mb_substr(trim((string) $lines[$j]), 0, 40, 'UTF-8');
                    }
                }

                return '(no anchor)';
            }
        }

        return '(unmatched)';
    }

    /**
     * @return array{0: ?string, 1: float}
     */
    private function propose(ResumeLineVectorizer $vectorizer, SectionClassifier $classifier, string $line): array
    {
        if (! isHeadingLikeStandalone($line)) {
            return [null, 0.0];
        }

        $prediction = $classifier->predictMany([$vectorizer->vectorize($line)])[0] ?? null;

        return $prediction ? [$prediction['label'], (float) $prediction['score']] : [null, 0.0];
    }

    private function dictionaryHeadings(): array
    {
        $reflection = new \ReflectionClass(ResumeParser::class);
        $constant = $reflection->getConstant('HEADINGS');

        return is_array($constant) ? $constant : [];
    }
}

function isHeadingLikeStandalone(string $line): bool
{
    $trimmed = trim($line);

    if ($trimmed === '' || mb_strlen($trimmed) > 100) {
        return false;
    }

    if (preg_match('/^[\x{2022}\x{25CF}\x{25E6}\x{25AA}\x{2023}\x{2219}\x{2013}\x{2014}*]\s?/u', $trimmed) === 1
        || preg_match('/\d{4}/', $trimmed) === 1
        || preg_match('/^(client|employer|current company|project name|role)\s*:/i', $trimmed) === 1
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
