<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\SkillCategory;
use Inertia\Inertia;
use Inertia\Response;

class SkillsController extends Controller
{
    public function index(): Response
    {
        $skills = Skill::with('category')
            ->orderBy('name')
            ->get()
            ->map(fn ($skill) => [
                'id' => $skill->id,
                'name' => $skill->name,
                'category_id' => $skill->category_id,
                'category' => $skill->category,
            ]);

        $categories = SkillCategory::orderBy('name')
            ->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]);

        $skillCount = $skills->count();

        return Inertia::render('Skills/Index', [
            'skills' => $skills,
            'categories' => $categories,
            'skillCount' => $skillCount,
        ]);
    }
}