<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportSkillsRequest;
use App\Http\Requests\Admin\StoreSkillRequest;
use App\Http\Requests\Admin\UpdateSkillRequest;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $categoryFilter = $request->get('category');

        $skills = Skill::with('category')
            ->when($search, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($categoryFilter, fn ($q, $v) => $q->where('category_id', $v))
            ->orderBy(SkillCategory::select('name')->whereColumn('id', 'skills.category_id'))
            ->orderBy('name')
            ->paginate(\App\Support\Settings::get('admin_page_size', 20))
            ->withQueryString();

        $categories = SkillCategory::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Skills/Index', [
            'skills' => $skills,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function store(StoreSkillRequest $request)
    {
        Skill::create($request->validated());

        return redirect()->route('admin.skills.index')
            ->with('success', 'Skill created successfully.');
    }

    public function update(UpdateSkillRequest $request, Skill $skill)
    {
        $skill->update($request->validated());

        return redirect()->route('admin.skills.index')
            ->with('success', 'Skill updated successfully.');
    }

    public function importTemplate(): HttpResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="skills-import-template.csv"',
        ];

        $columns = ['name', 'category_name'];
        $exampleRows = [
            ['Python', 'Programming Languages'],
            ['React', 'Frontend Development'],
            ['SQL', 'Data & Analytics'],
        ];

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $columns);
        foreach ($exampleRows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, $headers);
    }

    public function import(ImportSkillsRequest $request)
    {
        $file = $request->file('csv');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);

        $created = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $name = trim($row[0] ?? '');
            $categoryName = trim($row[1] ?? '');

            if (empty($name)) continue;

            $categoryId = null;
            if (!empty($categoryName)) {
                $category = SkillCategory::firstOrCreate(
                    ['name' => $categoryName],
                    ['slug' => Str::slug($categoryName)],
                );
                $categoryId = $category->id;
            }

            $existing = Skill::where('name', $name)->first();
            if ($existing) {
                $skipped++;
                continue;
            }

            Skill::create([
                'name' => $name,
                'category_id' => $categoryId,
            ]);
            $created++;
        }

        fclose($handle);

        return redirect()->route('admin.skills.index')
            ->with('success', "Import complete: {$created} created, {$skipped} skipped.");
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();

        return redirect()->route('admin.skills.index')
            ->with('success', 'Skill deleted successfully.');
    }
}
