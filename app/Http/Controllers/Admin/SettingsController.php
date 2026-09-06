<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => Settings::all(),
            'definitions' => config('settings.definitions'),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        Settings::save($request->validated());

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}