<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreJobPostingRequest;
use App\Http\Requests\Admin\UpdateJobPostingRequest;
use App\Models\JobPosting;
use App\Models\JobRequiredSkill;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobPostingController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');

        $postings = JobPosting::with('requiredSkills.skill.category')
            ->when($search, fn ($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('title', 'like', "%{$v}%")
                  ->orWhere('company', 'like', "%{$v}%");
            }))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $skills = Skill::with('category')
            ->orderBy(SkillCategory::select('name')->whereColumn('id', 'skills.category_id'))
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/JobPostings/Index', [
            'postings' => $postings,
            'skills' => $skills,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StoreJobPostingRequest $request)
    {
        $data = $request->validated();
        $skillsData = $data['skills'] ?? [];
        unset($data['skills']);

        $posting = JobPosting::create($data);

        foreach ($skillsData as $s) {
            JobRequiredSkill::create([
                'job_posting_id' => $posting->id,
                'skill_id' => $s['id'],
                'importance' => $s['importance'],
                'min_proficiency' => $s['min_proficiency'],
            ]);
        }

        return redirect()->route('admin.job-postings.index')
            ->with('success', 'Job posting created successfully.');
    }

    public function update(UpdateJobPostingRequest $request, JobPosting $jobPosting)
    {
        $data = $request->validated();
        $jobPosting->update($data);

        if ($request->has('skills')) {
            $jobPosting->requiredSkills()->delete();
            foreach ($data['skills'] as $s) {
                JobRequiredSkill::create([
                    'job_posting_id' => $jobPosting->id,
                    'skill_id' => $s['id'],
                    'importance' => $s['importance'],
                    'min_proficiency' => $s['min_proficiency'],
                ]);
            }
        }

        return redirect()->route('admin.job-postings.index')
            ->with('success', 'Job posting updated successfully.');
    }

    public function destroy(JobPosting $jobPosting)
    {
        $jobPosting->delete();

        return redirect()->route('admin.job-postings.index')
            ->with('success', 'Job posting deleted successfully.');
    }
}
