<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\Link;
use PhpOffice\PhpWord\Element\ListItem;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Title;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;

class ResumeTextExtractor
{
    public const SUPPORTED_EXTENSIONS = ['txt', 'pdf', 'docx'];

    public function supports(UploadedFile $file): bool
    {
        return in_array(strtolower($file->getClientOriginalExtension()), self::SUPPORTED_EXTENSIONS, true);
    }

    public function typeFor(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return in_array($extension, ['pdf', 'docx', 'txt'], true) ? $extension : 'txt';
    }

    public function extract(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'pdf' => $this->fromPdf($file),
            'docx' => $this->fromDocx($file),
            default => (string) $file->get(),
        };
    }

    private function fromPdf(UploadedFile $file): string
    {
        try {
            $text = (new PdfParser)
                ->parseFile($file->getRealPath() ?: (string) $file)
                ->getText();
        } catch (Throwable $e) {
            throw new RuntimeException('The PDF could not be read. Scanned/image-only PDFs are not supported.', 0, $e);
        }

        $cleaned = $this->clean($text);
        if (trim($cleaned) === '') {
            throw new RuntimeException('No readable text was found in the PDF (it may be a scanned image).');
        }

        return $cleaned;
    }

    private function fromDocx(UploadedFile $file): string
    {
        try {
            $document = IOFactory::load($file->getRealPath() ?: (string) $file);
            $text = '';
            if ($document instanceof PhpWord) {
                foreach ($document->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        $this->walk($element, $text);
                    }
                }
            }
        } catch (Throwable $e) {
            throw new RuntimeException('The DOCX file could not be read. Corrupted or legacy .doc files are not supported.', 0, $e);
        }

        $cleaned = $this->clean($text);
        if (trim($cleaned) === '') {
            throw new RuntimeException('No text was found in the DOCX file.');
        }

        return $cleaned;
    }

    private function walk(AbstractElement $element, string &$text): void
    {
        if ($element instanceof Text
            || $element instanceof ListItem
            || $element instanceof Link
            || $element instanceof Title
        ) {
            $text .= $element->getText()."\n";
        } elseif ($element instanceof TextRun) {
            foreach ($element->getElements() as $child) {
                $text .= ($child instanceof Text) ? $child->getText() : '';
            }
            $text .= "\n";
        } elseif ($element instanceof Table) {
            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    foreach ($cell->getElements() as $child) {
                        $this->walk($child, $text);
                    }
                    $text .= "\t";
                }
                $text .= "\n";
            }
        } elseif ($element instanceof AbstractContainer) {
            foreach ($element->getElements() as $child) {
                $this->walk($child, $text);
            }
        }
    }

    private function clean(string $text): string
    {
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $text) ?? '';
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? '';

        // Strip non-Latin scripts (Cyrillic, Arabic, Devanagari, CJK, Hangul, Thai, etc.) to
        // avoid skill-extraction false positives from non-Latin resumes.
        $text = preg_replace(
            '/[\x{0400}-\x{052F}\x{0590}-\x{08FF}\x{0900}-\x{097F}\x{0A80}-\x{0AFF}'
            .'\x{0B00}-\x{0B7F}\x{0C00}-\x{0C7F}\x{0D00}-\x{0D7F}\x{0E00}-\x{0E7F}'
            .'\x{1100}-\x{11FF}\x{1E00}-\x{1FFF}\x{2E80}-\x{2EFF}\x{3000}-\x{30FF}'
            .'\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{AC00}-\x{D7AF}\x{F900}-\x{FAFF}]/u',
            '',
            $text,
        ) ?? '';

        return $text;
    }
}
