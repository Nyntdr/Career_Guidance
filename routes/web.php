<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserSkillController;
use App\Http\Controllers\WorkExperienceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/onboarding/skip', function (\Illuminate\Http\Request $r) {
        $r->session()->put('onboarding_skipped', true);
        return redirect()->back();
    })->name('onboarding.skip');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/career', [ProfileController::class, 'updateCareer'])->name('profile.career.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/profile/education', [EducationController::class, 'store'])->name('profile.education.store');
    Route::patch('/profile/education/{education}', [EducationController::class, 'update'])->name('profile.education.update');
    Route::delete('/profile/education/{education}', [EducationController::class, 'destroy'])->name('profile.education.destroy');

    Route::post('/profile/work-experience', [WorkExperienceController::class, 'store'])->name('profile.work-experience.store');
    Route::patch('/profile/work-experience/{workExperience}', [WorkExperienceController::class, 'update'])->name('profile.work-experience.update');
    Route::delete('/profile/work-experience/{workExperience}', [WorkExperienceController::class, 'destroy'])->name('profile.work-experience.destroy');

    Route::post('/profile/skills', [UserSkillController::class, 'store'])->name('profile.skills.store');
    Route::patch('/profile/skills/{userSkill}', [UserSkillController::class, 'update'])->name('profile.skills.update');
    Route::delete('/profile/skills/{userSkill}', [UserSkillController::class, 'destroy'])->name('profile.skills.destroy');
});

require __DIR__.'/auth.php';
