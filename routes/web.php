<?php

use App\Http\Controllers\Admin\CareerRoleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\JobPostingController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\LearningResourceController;
use App\Http\Controllers\Admin\SkillCategoryController;
use App\Http\Controllers\Admin\SkillController as AdminSkillController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\EnsureUserRole;
use App\Http\Controllers\AtsScanController;
use App\Http\Controllers\CareersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\GapAssessmentController;
use App\Http\Controllers\JobsController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\SkillsController;
use App\Http\Controllers\UserSkillController;
use App\Http\Controllers\WorkExperienceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::aliasMiddleware('role', EnsureUserRole::class);

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
        Route::resource('skills', AdminSkillController::class)->except(['create', 'edit', 'show']);
        Route::get('skills/import-template', [AdminSkillController::class, 'importTemplate'])->name('skills.import-template');
        Route::post('skills/import', [AdminSkillController::class, 'import'])->name('skills.import');
        Route::resource('skill-categories', SkillCategoryController::class)->except(['create', 'edit', 'show']);
        Route::resource('job-postings', JobPostingController::class)->except(['create', 'edit', 'show']);
        Route::resource('learning-resources', LearningResourceController::class)->except(['create', 'edit', 'show']);
        Route::get('/reports', ReportController::class)->name('reports');
        Route::get('/users/{user}/profile', [AdminProfileController::class, 'show'])->name('users.profile');
        Route::resource('career-roles', CareerRoleController::class)->except(['create', 'edit', 'show'])->parameters([
            'career-roles' => 'career_role',
        ]);
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/careers', [CareersController::class, 'index'])->name('careers.index');
    Route::get('/careers/{careerRole}', [CareersController::class, 'show'])->name('careers.show');
    Route::get('/skills', [SkillsController::class, 'index'])->name('skills.index');
    Route::get('/learning', [LearningController::class, 'index'])->name('learning.index');
    Route::get('/jobs', [JobsController::class, 'index'])->name('jobs.index');
    Route::get('/roadmap', [RoadmapController::class, 'index'])->name('roadmap.index');
    Route::patch('/roadmap/progress/{progress}', [RoadmapController::class, 'updateProgress'])->name('roadmap.progress.update');
    Route::post('/assessment/run', [GapAssessmentController::class, 'run'])->name('assessment.run');

    Route::get('/resumes', [ResumeController::class, 'index'])->name('resume.index');
    Route::post('/resumes', [ResumeController::class, 'store'])->name('resume.store');
    Route::patch('/resumes/{resume}/primary', [ResumeController::class, 'markPrimary'])->name('resume.primary');
    Route::get('/resumes/{resume}/download', [ResumeController::class, 'download'])->name('resume.download');
    Route::delete('/resumes/{resume}', [ResumeController::class, 'destroy'])->name('resume.destroy');
    Route::post('/resumes/{resume}/scan', [ResumeController::class, 'scan'])->name('resume.scan');
    Route::get('/ats-scans/{scan}', [AtsScanController::class, 'show'])->name('ats.show');

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
