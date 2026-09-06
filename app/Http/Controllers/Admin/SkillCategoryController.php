<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSkillCategoryRequest;
use App\Http\Requests\Admin\UpdateSkillCategoryRequest;
use App\Models\SkillCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class SkillCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');

        $categories = SkillCategory::withCount('skills')
            ->when($search, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy('name')
            ->paginate(\App\Support\Settings::get('admin_page_size', 20))
            ->withQueryString();

        return Inertia::render('Admin/SkillCategories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StoreSkillCategoryRequest $request)
    {
        SkillCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('admin.skill-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function update(UpdateSkillCategoryRequest $request, SkillCategory $skillCategory)
    {
        $skillCategory->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('admin.skill-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(SkillCategory $skillCategory)
    {
        if ($skillCategory->skills()->count() > 0) {
            return redirect()->route('admin.skill-categories.index')
                ->with('error', 'Cannot delete category that has skills assigned.');
        }

        $skillCategory->delete();

        return redirect()->route('admin.skill-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
