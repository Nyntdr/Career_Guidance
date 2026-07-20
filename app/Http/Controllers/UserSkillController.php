<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserSkillRequest;
use App\Http\Requests\UpdateUserSkillRequest;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserSkillController extends Controller
{
    public function store(StoreUserSkillRequest $request): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $skill = Skill::firstOrCreate([
            'name' => $request->skill_name,
        ]);

        $profile->userSkills()->create([
            'skill_id' => $skill->id,
            'proficiency_level' => $request->proficiency_level,
            'source' => 'self_reported',
        ]);

        return $request->boolean('_onboarding')
            ? redirect()->route('dashboard')
            : redirect()->route('profile.edit');
    }

    public function update(UpdateUserSkillRequest $request, $userSkill): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $skill = $profile->userSkills()->findOrFail($userSkill);
        $skill->update([
            'proficiency_level' => $request->proficiency_level,
        ]);

        return redirect()->route('profile.edit');
    }

    public function destroy(Request $request, $userSkill): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $skill = $profile->userSkills()->findOrFail($userSkill);
        $skill->delete();

        return redirect()->route('profile.edit');
    }
}