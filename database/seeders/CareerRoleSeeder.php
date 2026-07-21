<?php

namespace Database\Seeders;

use App\Models\CareerRole;
use App\Models\CareerRoleSkill;
use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CareerRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'title' => 'Data Scientist',
                'description' => 'Analyze and interpret complex data to help organizations make decisions. Combines statistics, machine learning, and domain expertise.',
                'typical_seniority' => 'Mid to Senior',
                'avg_salary_range' => ['min' => 95000, 'max' => 145000],
                'growth_outlook' => 'High demand (+35% by 2030)',
                'skills' => [
                    'Python' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'SQL' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Machine Learning' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Statistics' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Data Visualization' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Deep Learning' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Communication' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Data Analysis' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Critical Thinking' => ['importance' => 'essential', 'min_proficiency' => 4],
                ],
            ],
            [
                'title' => 'Machine Learning Engineer',
                'description' => 'Design and build production ML systems. Focus on scaling models, building pipelines, and deploying AI solutions.',
                'typical_seniority' => 'Mid to Senior',
                'avg_salary_range' => ['min' => 110000, 'max' => 170000],
                'growth_outlook' => 'Very high demand (+40% by 2030)',
                'skills' => [
                    'Python' => ['importance' => 'essential', 'min_proficiency' => 5],
                    'Machine Learning' => ['importance' => 'essential', 'min_proficiency' => 5],
                    'Deep Learning' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'SQL' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Docker' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'AWS' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Git' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'CI/CD' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Statistics' => ['importance' => 'essential', 'min_proficiency' => 4],
                ],
            ],
            [
                'title' => 'Software Engineer',
                'description' => 'Design, develop, and maintain software systems. Work across the full stack or specialize in backend or frontend.',
                'typical_seniority' => 'Entry to Senior',
                'avg_salary_range' => ['min' => 80000, 'max' => 150000],
                'growth_outlook' => 'Steady demand (+22% by 2030)',
                'skills' => [
                    'Python' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Java' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'JavaScript' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'SQL' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Git' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Agile Methodologies' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Communication' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'API Design' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'CI/CD' => ['importance' => 'preferred', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'Data Analyst',
                'description' => 'Collect, process, and analyze data to uncover insights. Create reports and dashboards for business stakeholders.',
                'typical_seniority' => 'Entry to Mid',
                'avg_salary_range' => ['min' => 60000, 'max' => 95000],
                'growth_outlook' => 'Strong demand (+25% by 2030)',
                'skills' => [
                    'SQL' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Data Analysis' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Data Visualization' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Statistics' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Python' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Communication' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Critical Thinking' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'Product Manager',
                'description' => 'Define product vision, strategy, and roadmap. Work cross-functionally with engineering, design, and business teams.',
                'typical_seniority' => 'Mid to Senior',
                'avg_salary_range' => ['min' => 90000, 'max' => 160000],
                'growth_outlook' => 'High demand (+20% by 2030)',
                'skills' => [
                    'Project Management' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Communication' => ['importance' => 'essential', 'min_proficiency' => 5],
                    'Leadership' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Agile Methodologies' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Data Analysis' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Critical Thinking' => ['importance' => 'essential', 'min_proficiency' => 4],
                ],
            ],
            [
                'title' => 'DevOps Engineer',
                'description' => 'Build and maintain CI/CD pipelines, manage cloud infrastructure, and automate deployment processes.',
                'typical_seniority' => 'Mid to Senior',
                'avg_salary_range' => ['min' => 95000, 'max' => 155000],
                'growth_outlook' => 'High demand (+30% by 2030)',
                'skills' => [
                    'AWS' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Docker' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Kubernetes' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'CI/CD' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Git' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Python' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Communication' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 4],
                ],
            ],
            [
                'title' => 'Frontend Developer',
                'description' => 'Build responsive, performant user interfaces. Work with modern frameworks to create engaging web experiences.',
                'typical_seniority' => 'Entry to Senior',
                'avg_salary_range' => ['min' => 70000, 'max' => 140000],
                'growth_outlook' => 'Steady demand (+20% by 2030)',
                'skills' => [
                    'JavaScript' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'TypeScript' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'React' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Git' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'API Design' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Communication' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Agile Methodologies' => ['importance' => 'preferred', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'Backend Developer',
                'description' => 'Design and build server-side logic, APIs, and database integrations. Ensure performance, scalability, and security.',
                'typical_seniority' => 'Entry to Senior',
                'avg_salary_range' => ['min' => 75000, 'max' => 145000],
                'growth_outlook' => 'Steady demand (+22% by 2030)',
                'skills' => [
                    'PHP' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Python' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Java' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Node.js' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'SQL' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'API Design' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Git' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Docker' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'AWS' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 4],
                ],
            ],
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        CareerRoleSkill::truncate();
        CareerRole::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($roles as $roleData) {
            $skills = $roleData['skills'];
            unset($roleData['skills']);

            $role = CareerRole::create($roleData);

            foreach ($skills as $skillName => $pivot) {
                $skill = Skill::where('name', $skillName)->first();
                if ($skill) {
                    CareerRoleSkill::create([
                        'career_role_id' => $role->id,
                        'skill_id' => $skill->id,
                        'importance' => $pivot['importance'],
                        'min_proficiency' => $pivot['min_proficiency'],
                    ]);
                }
            }
        }
    }
}
