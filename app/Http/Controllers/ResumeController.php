<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\Resume;
use App\Services\AtsScanner;
use App\Services\ResumeParser;
use App\Services\ResumeTextExtractor;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResumeController extends Controller
{
    public function index(Request $request): Response
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $resumes = Resume::where('profile_id', $profile->id)
            ->with(['sections', 'atsScans'])
            ->latest('uploaded_at')
            ->get()
            ->map(fn ($resume) => [
                'id' => $resume->id,
                'file_url' => $resume->file_url,
                'file_type' => $resume->file_type,
                'version' => $resume->version,
                'is_primary' => $resume->is_primary,
                'uploaded_at' => $resume->uploaded_at?->toDateTimeString(),
                'sections' => $resume->sections->sortBy('order_index')->values(),
                'skills' => $resume->parsed_json['skills'] ?? [],
                'latest_scan' => $resume->atsScans->sortByDesc('created_at')->first()?->id,
            ]);

        $jobs = JobPosting::orderBy('title')
            ->get(['id', 'title', 'company']);

        return Inertia::render('Resumes/Index', [
            'resumes' => $resumes,
            'jobs' => $jobs,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $validated = $request->validate([
            'resume_file' => ['nullable', 'file', 'mimes:txt,pdf,docx', 'max:5120'],
            'content' => ['nullable', 'string', 'max:50000'],
        ]);

        if (empty($validated['resume_file']) && empty($validated['content'])) {
            return redirect()->route('resume.index')->with('error', 'Upload a .txt, .pdf, or .docx file, or paste your resume text.');
        }

        $fileUrl = null;
        $fileType = 'text';
        $fileName = null;

        if (!empty($validated['resume_file'])) {
            $file = $validated['resume_file'];
            $stored = $file->store('resumes', 'local');
            $fileName = $file->getClientOriginalName();
            $fileUrl = $stored;
            $fileType = app(ResumeTextExtractor::class)->typeFor($file);

            try {
                $rawText = app(ResumeTextExtractor::class)->extract($file);
            } catch (RuntimeException $e) {
                Storage::disk('local')->delete($stored);

                return redirect()->route('resume.index')->with('error', $e->getMessage());
            }
        } else {
            $rawText = $validated['content'];
        }

        if (!$rawText || trim($rawText) === '') {
            return redirect()->route('resume.index')->with('error', 'The resume text is empty.');
        }

        app(ResumeParser::class)->parseAndStore($profile, $rawText, $fileName, $fileUrl, $fileType);

        return redirect()->route('resume.index')->with('success', 'Resume uploaded and parsed successfully.');
    }

    public function markPrimary(Request $request, Resume $resume): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);
        $this->authorizeOwnership($profile, $resume);

        Resume::where('profile_id', $profile->id)->update(['is_primary' => false]);
        $resume->update(['is_primary' => true]);

        return redirect()->route('resume.index');
    }

    public function download(Request $request, Resume $resume): BinaryFileResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);
        $this->authorizeOwnership($profile, $resume);

        abort_if(!$resume->file_url, 404);

        $path = Storage::disk('local')->path($resume->file_url);

        abort_if(!file_exists($path), 404);

        $fileName = $resume->parsed_json['source_filename'] ?? 'resume.txt';

        return Storage::disk('local')->download($resume->file_url, $fileName);
    }

    public function destroy(Request $request, Resume $resume): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);
        $this->authorizeOwnership($profile, $resume);

        if ($resume->file_url) {
            Storage::disk('local')->delete($resume->file_url);
        }

        $resume->delete();

        return redirect()->route('resume.index')->with('success', 'Resume deleted.');
    }

    public function scan(Request $request, Resume $resume): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);
        $this->authorizeOwnership($profile, $resume);

        $validated = $request->validate([
            'job_posting_id' => ['required', 'exists:job_postings,id'],
        ]);

        $job = JobPosting::findOrFail($validated['job_posting_id']);

        if (!$resume->raw_text) {
            return redirect()->route('resume.index')->with('error', 'This resume has no parseable text.');
        }

        $scan = app(AtsScanner::class)->scan($resume, $job);

        return redirect()->route('ats.show', $scan)->with('success', 'Resume scanned against the selected job.');
    }

    private function authorizeOwnership($profile, Resume $resume): void
    {
        abort_unless($resume->profile_id === $profile->id, 403);
    }
}