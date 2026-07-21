<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCareerRoleRequest;
use App\Http\Requests\Admin\UpdateCareerRoleRequest;
use App\Models\CareerRole;
use App\Models\CareerRoleSkill;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CareerRoleController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');

        $roles = CareerRole::with('requiredSkills')
            ->withCount('skills')
            ->when($search, fn ($q, $v) => $q->where('title', 'like', "%{$v}%"))
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        $skills = Skill::with('category')
            ->orderBy(SkillCategory::select('name')->whereColumn('id', 'skills.category_id'))
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/CareerRoles/Index', [
            'roles' => $roles,
            'skills' => $skills,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StoreCareerRoleRequest $request)
    {
        $data = $request->validated();
        $skillsData = $data['skills'];
        unset($data['skills']);

        $role = CareerRole::create($data);

        foreach ($skillsData as $s) {
            CareerRoleSkill::create([
                'career_role_id' => $role->id,
                'skill_id' => $s['id'],
                'importance' => $s['importance'],
                'min_proficiency' => $s['min_proficiency'],
            ]);
        }

        return redirect()->route('admin.career-roles.index')
            ->with('success', 'Career role created successfully.');
    }

    public function update(UpdateCareerRoleRequest $request, CareerRole $careerRole)
    {
        $data = $request->validated();
        $careerRole->update($data);

        if ($request->has('skills')) {
            $careerRole->requiredSkills()->delete();
            foreach ($data['skills'] as $s) {
                CareerRoleSkill::create([
                    'career_role_id' => $careerRole->id,
                    'skill_id' => $s['id'],
                    'importance' => $s['importance'],
                    'min_proficiency' => $s['min_proficiency'],
                ]);
            }
        }

        return redirect()->route('admin.career-roles.index')
            ->with('success', 'Career role updated successfully.');
    }

    public function destroy(CareerRole $careerRole)
    {
        $careerRole->delete();

        return redirect()->route('admin.career-roles.index')
            ->with('success', 'Career role deleted successfully.');
    }
}
