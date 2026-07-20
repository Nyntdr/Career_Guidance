<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkExperienceRequest;
use App\Http\Requests\UpdateWorkExperienceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkExperienceController extends Controller
{
    public function store(StoreWorkExperienceRequest $request): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $profile->workExperience()->create($request->validated());

        return $request->boolean('_onboarding')
            ? redirect()->route('dashboard')
            : redirect()->route('profile.edit');
    }

    public function update(UpdateWorkExperienceRequest $request, $workExperience): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $work = $profile->workExperience()->findOrFail($workExperience);
        $work->update($request->validated());

        return redirect()->route('profile.edit');
    }

    public function destroy(Request $request, $workExperience): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $work = $profile->workExperience()->findOrFail($workExperience);
        $work->delete();

        return redirect()->route('profile.edit');
    }
}