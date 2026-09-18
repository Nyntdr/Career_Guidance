<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(User $user): Response
    {
        $user->load(['profile.education', 'profile.workExperience', 'profile.userSkills.skill']);

        return Inertia::render('Admin/UserProfile', [
            'targetUser' => $user,
        ]);
    }
}
