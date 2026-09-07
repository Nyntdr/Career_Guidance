<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\AbstractElement;
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
            $text = (new PdfParser())
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
            $document = \PhpOffice\PhpWord\IOFactory::load($file->getRealPath() ?: (string) $file);
            $text = '';
            if ($document instanceof \PhpOffice\PhpWord\PhpWord) {
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
        if ($element instanceof \PhpOffice\PhpWord\Element\Text
            || $element instanceof \PhpOffice\PhpWord\Element\ListItem
            || $element instanceof \PhpOffice\PhpWord\Element\Link
            || $element instanceof \PhpOffice\PhpWord\Element\Title
        ) {
            $text .= $element->getText()."\n";
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
            foreach ($element->getElements() as $child) {
                $text .= ($child instanceof \PhpOffice\PhpWord\Element\Text) ? $child->getText() : '';
            }
            $text .= "\n";
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
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

        return $text;
    }
}