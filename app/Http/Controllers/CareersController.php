<?php

namespace App\Http\Controllers;

use App\Models\CareerRole;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CareersController extends Controller
{
    public function index(Request $request): Response
    {
        $roles = CareerRole::query()
            ->withCount('requiredSkills')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search'));

                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('title')
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('Careers/Index', [
            'roles' => $roles,
            'filters' => ['search' => $request->string('search')->toString() ?? ''],
        ]);
    }

    public function show(CareerRole $careerRole): Response
    {
        $careerRole->load(['requiredSkills' => function ($query) {
            $query->orderBy('career_role_skills.importance')
                ->orderBy('career_role_skills.min_proficiency', 'desc');
        }]);

        $requiredSkills = collect($careerRole->requiredSkills)->map(function ($skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'importance' => $skill->pivot->importance,
                'min_proficiency' => $skill->pivot->min_proficiency,
            ];
        });

        $skillsByImportance = [
            'essential' => $requiredSkills->where('importance', 'essential')->values(),
            'preferred' => $requiredSkills->where('importance', 'preferred')->values(),
        ];

        $skillCount = $careerRole->requiredSkills->count();

        $relatedRoles = CareerRole::query()
            ->where('id', '!=', $careerRole->id)
            ->withCount('requiredSkills')
            ->orderBy('title')
            ->take(4)
            ->get();

        return Inertia::render('Careers/Show', [
            'role' => $careerRole,
            'skillsByImportance' => $skillsByImportance,
            'skillCount' => $skillCount,
            'relatedRoles' => $relatedRoles,
        ]);
    }
}