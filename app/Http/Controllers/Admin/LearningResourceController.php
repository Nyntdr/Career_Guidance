<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLearningResourceRequest;
use App\Http\Requests\Admin\UpdateLearningResourceRequest;
use App\Models\LearningResource;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearningResourceController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $typeFilter = $request->get('type');

        $resources = LearningResource::with('skills')
            ->when($search, fn ($q, $v) => $q->where('title', 'like', "%{$v}%"))
            ->when($typeFilter, fn ($q, $v) => $q->where('resource_type', $v))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $skills = Skill::with('category')
            ->orderBy(SkillCategory::select('name')->whereColumn('id', 'skills.category_id'))
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/LearningResources/Index', [
            'resources' => $resources,
            'skills' => $skills,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function store(StoreLearningResourceRequest $request)
    {
        $data = $request->validated();
        $skillIds = $data['skill_ids'] ?? [];
        unset($data['skill_ids']);

        $resource = LearningResource::create($data);
        $resource->skills()->sync($skillIds);

        return redirect()->route('admin.learning-resources.index')
            ->with('success', 'Learning resource created successfully.');
    }

    public function update(UpdateLearningResourceRequest $request, LearningResource $learningResource)
    {
        $data = $request->validated();
        $skillIds = $data['skill_ids'] ?? [];
        unset($data['skill_ids']);

        $learningResource->update($data);
        $learningResource->skills()->sync($skillIds);

        return redirect()->route('admin.learning-resources.index')
            ->with('success', 'Learning resource updated successfully.');
    }

    public function destroy(LearningResource $learningResource)
    {
        $learningResource->delete();

        return redirect()->route('admin.learning-resources.index')
            ->with('success', 'Learning resource deleted successfully.');
    }
}
