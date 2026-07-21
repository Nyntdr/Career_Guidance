<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $candidates = User::where('role', 'candidate')
            ->with('profile.userSkills.skill')
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'skillsCount' => $u->profile?->userSkills->count() ?? 0,
                'joined' => $u->created_at->diffForHumans(),
            ]);

        return Inertia::render('Counselor/Dashboard', [
            'candidates' => $candidates,
        ]);
    }
}
