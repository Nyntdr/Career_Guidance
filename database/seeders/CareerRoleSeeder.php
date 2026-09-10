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
                'description' => 'Analyze and interpret complex data to help Nepali banks, telecoms (NTC, Ncell), and e-commerce platforms make decisions. Combines statistics, machine learning, and domain expertise.',
                'typical_seniority' => 'Mid to Senior',
                'avg_salary_range' => ['min' => 480000, 'max' => 1920000],
                'growth_outlook' => 'High demand — an emerging field in Nepal with few qualified professionals',
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
                'description' => 'Design and build production ML systems for Nepali AI startups, fintechs, and remote international teams. Focus on scaling models, pipelines, and deploying AI solutions.',
                'typical_seniority' => 'Mid to Senior',
                'avg_salary_range' => ['min' => 600000, 'max' => 2400000],
                'growth_outlook' => 'Very high demand — AI and remote roles pay top of the Nepali market',
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
                'description' => 'Design, develop, and maintain software systems for Nepali IT companies, banks, and international clients (outsourcing/remote). Work across the full stack or specialize in backend or frontend.',
                'typical_seniority' => 'Entry to Senior',
                'avg_salary_range' => ['min' => 384000, 'max' => 1800000],
                'growth_outlook' => 'Strong demand — Nepal\'s IT outsourcing and fintech sectors are growing rapidly',
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
                'description' => 'Collect, process, and analyze data for Nepali banks, MFIs, NGOs/INGOs, and businesses. Create reports and dashboards for stakeholders. Excel and SQL skills are highly valued locally.',
                'typical_seniority' => 'Entry to Mid',
                'avg_salary_range' => ['min' => 288000, 'max' => 960000],
                'growth_outlook' => 'Strong demand — banks, MFIs, and NGOs in Nepal are investing in data roles',
                'skills' => [
                    'SQL' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Data Analysis' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Data Visualization' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Statistics' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Microsoft Excel' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Python' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Communication' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Critical Thinking' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'Product Manager',
                'description' => 'Define product vision, strategy, and roadmap for Nepali startups and fintechs (eSewa, Khalti, Fonepay) and software houses. Work cross-functionally with engineering, design, and business teams.',
                'typical_seniority' => 'Mid to Senior',
                'avg_salary_range' => ['min' => 600000, 'max' => 2160000],
                'growth_outlook' => 'High demand — Nepal\'s startup and digital payment ecosystems are expanding',
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
                'description' => 'Build and maintain CI/CD pipelines, manage cloud infrastructure, and automate deployments for Nepali software companies, ISPs, and hosting providers.',
                'typical_seniority' => 'Mid to Senior',
                'avg_salary_range' => ['min' => 540000, 'max' => 1920000],
                'growth_outlook' => 'High demand — cloud adoption is accelerating among Nepali enterprises',
                'skills' => [
                    'AWS' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Docker' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Kubernetes' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'CI/CD' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Git' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Python' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Networking' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 4],
                ],
            ],
            [
                'title' => 'Frontend Developer',
                'description' => 'Build responsive, performant user interfaces for Nepali web agencies, startups, and remote clients. Work with modern frameworks to create engaging web experiences.',
                'typical_seniority' => 'Entry to Senior',
                'avg_salary_range' => ['min' => 336000, 'max' => 1440000],
                'growth_outlook' => 'Steady demand — most Nepali software houses hire frontend developers regularly',
                'skills' => [
                    'JavaScript' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'TypeScript' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'React' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Vue.js' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Git' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'UI/UX Design' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'API Design' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Communication' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Agile Methodologies' => ['importance' => 'preferred', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'Backend Developer',
                'description' => 'Design and build server-side logic, APIs, and database integrations. Laravel and Node.js stacks are especially in demand across Nepali software companies.',
                'typical_seniority' => 'Entry to Senior',
                'avg_salary_range' => ['min' => 336000, 'max' => 1560000],
                'growth_outlook' => 'Steady demand — Laravel remains the most requested stack in Nepal',
                'skills' => [
                    'PHP' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Laravel' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Python' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Node.js' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'SQL' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'API Design' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Git' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Docker' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'AWS' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 4],
                ],
            ],
            [
                'title' => 'Mobile App Developer',
                'description' => 'Build Android/iOS apps for Nepali fintech, food delivery, and e-wallet startups. Flutter and native Android skills are most in demand locally.',
                'typical_seniority' => 'Entry to Senior',
                'avg_salary_range' => ['min' => 336000, 'max' => 1440000],
                'growth_outlook' => 'High demand — mobile-first products dominate Nepal\'s startup scene',
                'skills' => [
                    'Flutter' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Android Development' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'React Native' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'JavaScript' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'API Design' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Git' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 4],
                ],
            ],
            [
                'title' => 'UI/UX Designer',
                'description' => 'Design intuitive user interfaces and experiences for Nepali digital products — from banking apps to e-commerce. Translate user research into wireframes and prototypes.',
                'typical_seniority' => 'Entry to Senior',
                'avg_salary_range' => ['min' => 300000, 'max' => 1080000],
                'growth_outlook' => 'Growing demand as Nepali companies invest in digital products',
                'skills' => [
                    'UI/UX Design' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Figma' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Adobe Photoshop' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Graphic Design' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Communication' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Critical Thinking' => ['importance' => 'preferred', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'QA Engineer',
                'description' => 'Test software products to ensure quality before release. Manual and automated testing skills are sought by Nepali software houses and outsourcing firms.',
                'typical_seniority' => 'Entry to Mid',
                'avg_salary_range' => ['min' => 288000, 'max' => 960000],
                'growth_outlook' => 'Steady demand — quality processes are becoming standard in Nepali IT companies',
                'skills' => [
                    'Quality Assurance' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Agile Methodologies' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'SQL' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Python' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'API Design' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Communication' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 4],
                ],
            ],
            [
                'title' => 'Digital Marketing Specialist',
                'description' => 'Run social media, SEO, and paid campaigns for Nepali brands, e-commerce, and tourism businesses. Facebook/Instagram and Google Ads dominate the local landscape.',
                'typical_seniority' => 'Entry to Mid',
                'avg_salary_range' => ['min' => 240000, 'max' => 840000],
                'growth_outlook' => 'High demand — every Nepali business is moving online',
                'skills' => [
                    'Digital Marketing' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'SEO' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Social Media Marketing' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Content Writing' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Data Analysis' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Microsoft Excel' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Communication' => ['importance' => 'essential', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'Accountant',
                'description' => 'Manage bookkeeping, tax filings (VAT, PAN), and financial reporting for Nepali businesses. Knowledge of NRB regulations and Tally ERP is expected in most local jobs.',
                'typical_seniority' => 'Entry to Senior',
                'avg_salary_range' => ['min' => 264000, 'max' => 720000],
                'growth_outlook' => 'Steady demand — every registered business in Nepal needs accounting staff',
                'skills' => [
                    'Accounting' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Tally ERP' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Microsoft Excel' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Data Analysis' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Communication' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Critical Thinking' => ['importance' => 'preferred', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'Civil Engineer',
                'description' => 'Plan and supervise construction of roads, hydropower projects, and buildings. Nepal\'s infrastructure boom and hydropower sector offer strong opportunities.',
                'typical_seniority' => 'Entry to Senior',
                'avg_salary_range' => ['min' => 300000, 'max' => 1200000],
                'growth_outlook' => 'Steady demand — driven by hydropower, road, and urban development projects',
                'skills' => [
                    'AutoCAD' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Surveying' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Microsoft Excel' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Project Management' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Leadership' => ['importance' => 'preferred', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'Registered Nurse',
                'description' => 'Provide patient care in hospitals, clinics, and community health posts. Nursing remains one of the most stable careers in Nepal, with strong demand at home and abroad.',
                'typical_seniority' => 'Entry to Senior',
                'avg_salary_range' => ['min' => 192000, 'max' => 600000],
                'growth_outlook' => 'High demand — chronic shortage of nurses in Nepali hospitals and abroad',
                'skills' => [
                    'Patient Care' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Communication' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Critical Thinking' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Leadership' => ['importance' => 'preferred', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'Bank Officer',
                'description' => 'Work in commercial banks or microfinance institutions handling credit, operations, or customer relations. Banking remains one of the most sought-after careers in Nepal.',
                'typical_seniority' => 'Entry to Senior',
                'avg_salary_range' => ['min' => 360000, 'max' => 1200000],
                'growth_outlook' => 'Steady demand — Nepal\'s banking and microfinance sectors keep expanding',
                'skills' => [
                    'Accounting' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Microsoft Excel' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Communication' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Customer Service' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Data Analysis' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Critical Thinking' => ['importance' => 'preferred', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'Network Engineer',
                'description' => 'Design, install, and maintain network infrastructure for ISPs, telecoms, banks, and data centers. Networking certifications (CCNA/CCNP) are highly valued in Nepal.',
                'typical_seniority' => 'Entry to Senior',
                'avg_salary_range' => ['min' => 300000, 'max' => 1200000],
                'growth_outlook' => 'Steady demand — internet penetration and data centers are growing in Nepal',
                'skills' => [
                    'Networking' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Network Security' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'AWS' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Docker' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Communication' => ['importance' => 'preferred', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'Customer Service Representative',
                'description' => 'Handle customer queries for banks, telecoms, and BPO/KPO call centers serving international clients. Strong English and Nepali communication is essential.',
                'typical_seniority' => 'Entry to Mid',
                'avg_salary_range' => ['min' => 216000, 'max' => 480000],
                'growth_outlook' => 'Growing demand — Nepal\'s BPO industry is expanding with international clients',
                'skills' => [
                    'Customer Service' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Communication' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Problem Solving' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Microsoft Excel' => ['importance' => 'preferred', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'Graphic Designer',
                'description' => 'Create visual assets for brands, agencies, and digital platforms — logos, social media creatives, print materials. Freelancing and agency work are common in Nepal.',
                'typical_seniority' => 'Entry to Mid',
                'avg_salary_range' => ['min' => 216000, 'max' => 720000],
                'growth_outlook' => 'Steady demand — advertising and social media marketing keep growing',
                'skills' => [
                    'Graphic Design' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Adobe Photoshop' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Figma' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Communication' => ['importance' => 'preferred', 'min_proficiency' => 3],
                    'Critical Thinking' => ['importance' => 'preferred', 'min_proficiency' => 3],
                ],
            ],
            [
                'title' => 'Content Writer',
                'description' => 'Write blogs, website copy, and marketing content for Nepali businesses and international clients. English fluency and SEO knowledge open remote work opportunities.',
                'typical_seniority' => 'Entry to Mid',
                'avg_salary_range' => ['min' => 192000, 'max' => 600000],
                'growth_outlook' => 'Growing demand — content marketing and remote freelancing are on the rise',
                'skills' => [
                    'Content Writing' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'SEO' => ['importance' => 'essential', 'min_proficiency' => 3],
                    'Communication' => ['importance' => 'essential', 'min_proficiency' => 4],
                    'Digital Marketing' => ['importance' => 'preferred', 'min_proficiency' => 3],
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
