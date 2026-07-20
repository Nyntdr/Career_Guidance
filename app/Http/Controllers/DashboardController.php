<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'careerPaths' => 10,
                'avgCompatibility' => 84,
                'skillsAssessed' => 18,
                'roadmapSteps' => 5,
            ],
            'topMatch' => [
                'title' => 'Data Scientists',
                'compatibility' => 87,
                'description' => 'Strong fit based on your analytical skills, interest in technology, and problem-solving aptitude.',
                'salaryRange' => '$95,000 – $145,000',
                'growth' => 'High demand (+35% by 2030)',
            ],
            'careerMatches' => [
                ['title' => 'Data Scientist', 'score' => 87, 'category' => 'Technology'],
                ['title' => 'Machine Learning Engineer', 'score' => 82, 'category' => 'Technology'],
                ['title' => 'Data Analyst', 'score' => 79, 'category' => 'Analytics'],
                ['title' => 'Business Intelligence Analyst', 'score' => 74, 'category' => 'Analytics'],
                ['title' => 'Product Manager', 'score' => 68, 'category' => 'Management'],
            ],
            'skillsToDevelop' => [
                ['name' => 'Python', 'priority' => 'High', 'current' => 65, 'target' => 90],
                ['name' => 'Machine Learning', 'priority' => 'High', 'current' => 45, 'target' => 85],
                ['name' => 'Statistics', 'priority' => 'Medium', 'current' => 70, 'target' => 88],
                ['name' => 'SQL', 'priority' => 'Medium', 'current' => 55, 'target' => 80],
                ['name' => 'Data Visualization', 'priority' => 'Low', 'current' => 40, 'target' => 75],
            ],
            'profileSummary' => [
                'education' => 'Bachelor of Science in Computer Science',
                'interests' => ['Artificial Intelligence', 'Data Analysis', 'Research', 'Problem Solving'],
                'goals' => 'Transition into a data-driven role within the next 12 months',
            ],
            'recentActivity' => [
                ['action' => 'Career assessment completed', 'date' => 'Jul 12, 2026'],
                ['action' => 'Skill gap analysis generated', 'date' => 'Jul 12, 2026'],
                ['action' => 'Profile updated with new interests', 'date' => 'Jul 10, 2026'],
                ['action' => 'Roadmap created for Data Scientist path', 'date' => 'Jul 8, 2026'],
            ],
        ]);
    }
}
