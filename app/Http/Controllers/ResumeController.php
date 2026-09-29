<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\Resume;
use App\Services\AtsScanner;
use App\Services\RecommendationEngine;
use App\Services\ResumeParser;
use App\Services\ResumeTextExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'resumeLikeness' => session('resume_likeness'),
        ]);
    }

    public function store(\App\Http\Requests\StoreResumeRequest $request): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);
        $validated = $request->validated();

        $fileUrl = null;
        $fileType = 'text';
        $fileName = null;
        $stored = null;

        if (! empty($validated['resume_file'])) {
            $file = $validated['resume_file'];
            $stored = $file->store('resumes', 'local');
            $fileName = $file->getClientOriginalName();
            $fileUrl = $stored;
            $fileType = app(ResumeTextExtractor::class)->typeFor($file);

            try {
                $rawText = app(ResumeTextExtractor::class)->extract($file);
            } catch (RuntimeException $e) {
                Storage::disk('local')->delete($stored);

                return redirect()->back()->withErrors(['resume_file' => $e->getMessage()]);
            }
        } else {
            // Pasted text bypasses the extractor, so normalise it here to keep
            // the non-Latin-script guard and whitespace collapsing in effect.
            $rawText = app(ResumeTextExtractor::class)->clean($validated['content']);
        }

        if (! $rawText || trim($rawText) === '') {
            if ($stored) {
                Storage::disk('local')->delete($stored);
            }

            return redirect()->back()->withErrors(['resume_file' => 'The resume text is empty.']);
        }

        $verdict = app(\App\Services\ResumeValidator::class)->assess($rawText);

        if ($verdict['band'] === 'not_a_resume') {
            if ($stored) {
                Storage::disk('local')->delete($stored);
            }

            $message = $verdict['reasons'][0] ?? "That file doesn't look like a resume — we couldn't find any experience, education, or skills sections. Check you uploaded the right document.";

            return redirect()->back()->withErrors(['resume_file' => $message]);
        }

        if ($verdict['band'] === 'borderline') {
            session()->flash('resume_likeness', $verdict);
        }

        $beforeSkillIds = $profile->userSkills()->pluck('skill_id')->all();

        $resume = app(ResumeParser::class)->parseAndStore($profile, $rawText, $fileName, $fileUrl, $fileType);

        $parsedSkills = $resume->parsed_json['skills'] ?? [];

        $addedSkills = array_diff($profile->userSkills()->pluck('skill_id')->all(), $beforeSkillIds);

        if (empty($parsedSkills)) {
            return redirect()->route('resume.index')->with(
                'warning',
                'Resume uploaded and sections were found, but no skills were detected. '
                .'Add a dedicated Skills section, or add skills manually from your profile. '
                .'Next: run an ATS scan against a job, or view your recommendations.'
            );
        }

        $names = array_slice(array_column($parsedSkills, 'name'), 0, 5);
        $summary = count($parsedSkills) > 5
            ? implode(', ', $names).' and more'
            : implode(', ', $names);

        $refreshNote = '';

        if (! empty($addedSkills)) {
            $matched = app(RecommendationEngine::class)->generate($profile)
                ->pluck('career_role_id')
                ->count();

            if ($matched > 0) {
                $refreshNote = ' Your career recommendations were refreshed with the updated skill set.';
            }
        }

        return redirect()->route('resume.index')->with(
            'success',
            'Resume uploaded and parsed successfully. '.count($parsedSkills)
            .' skill'.(count($parsedSkills) === 1 ? '' : 's')
            ." detected and added to your profile ({$summary})."
            .$refreshNote
            .' Next: run an ATS scan against a job, or view your recommendations.'
        );
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

        abort_if(! $resume->file_url, 404);

        $path = Storage::disk('local')->path($resume->file_url);

        abort_if(! file_exists($path), 404);

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

    public function scan(\App\Http\Requests\StoreAtsScanRequest $request, Resume $resume): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);
        $this->authorizeOwnership($profile, $resume);

        $validated = $request->validated();

        $job = JobPosting::findOrFail($validated['job_posting_id']);

        if (! $resume->raw_text) {
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
