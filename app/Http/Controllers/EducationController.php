<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEducationRequest;
use App\Http\Requests\UpdateEducationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    public function store(StoreEducationRequest $request): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $profile->education()->create($request->validated());

        return $request->boolean('_onboarding')
            ? redirect()->route('dashboard')
            : redirect()->route('profile.edit');
    }

    public function update(UpdateEducationRequest $request, $education): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $edu = $profile->education()->findOrFail($education);
        $edu->update($request->validated());

        return redirect()->route('profile.edit');
    }

    public function destroy(Request $request, $education): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate([]);

        $edu = $profile->education()->findOrFail($education);
        $edu->delete();

        return redirect()->route('profile.edit');
    }
}