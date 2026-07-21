<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerRole;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $raw = User::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as count")
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->pluck('count', 'month');

        $labels = [];
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $labels[] = $month;
            $data[] = (int) ($raw[$month] ?? 0);
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers' => User::count(),
                'totalCandidates' => User::where('role', 'candidate')->count(),
                'totalCounselors' => User::where('role', 'counselor')->count(),
                'totalAdmins' => User::where('role', 'admin')->count(),
                'totalCareerRoles' => CareerRole::count(),
                'totalSkills' => Skill::count(),
            ],
            'recentUsers' => User::select('id', 'name', 'email', 'role', 'created_at')
                ->latest()
                ->take(10)
                ->get(),
            'skillCategoryCounts' => SkillCategory::withCount('skills')
                ->orderBy('name')
                ->get(['id', 'name']),
            'userRegistrationsChart' => [
                'labels' => $labels,
                'data' => $data,
            ],
            'roleDistributionChart' => [
                'labels' => ['Candidates', 'Counselors', 'Admins'],
                'data' => [
                    User::where('role', 'candidate')->count(),
                    User::where('role', 'counselor')->count(),
                    User::where('role', 'admin')->count(),
                ],
            ],
        ]);
    }
}
