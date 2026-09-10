<?php

namespace Database\Seeders;

use App\Models\JobPosting;
use App\Models\JobRequiredSkill;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class JobPostingSeeder extends Seeder
{
    public function run(): void
    {
        $postings = [
            [
                'title' => 'Senior Laravel Developer',
                'company' => 'Leapfrog Technology',
                'location' => 'Kathmandu, Nepal',
                'seniority_level' => 'Senior',
                'raw_description' => 'Build and scale web platforms for international clients. Strong Laravel and API experience required; remote-friendly hybrid work from our Durbarmarg office.',
                'posted_at' => now()->subDays(2),
                'skills' => [
                    'PHP' => ['required', 4],
                    'Laravel' => ['required', 5],
                    'SQL' => ['required', 4],
                    'API Design' => ['required', 4],
                    'Git' => ['required', 3],
                    'Docker' => ['preferred', 3],
                    'AWS' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Machine Learning Engineer',
                'company' => 'Fusemachines',
                'location' => 'Kathmandu, Nepal',
                'seniority_level' => 'Mid to Senior',
                'raw_description' => 'Design and deploy production ML pipelines for global AI products. Work with a world-class AI team from our Kathmandu office.',
                'posted_at' => now()->subDays(5),
                'skills' => [
                    'Python' => ['required', 4],
                    'Machine Learning' => ['required', 4],
                    'Deep Learning' => ['required', 4],
                    'Docker' => ['required', 3],
                    'AWS' => ['required', 3],
                    'Statistics' => ['preferred', 3],
                    'Git' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Full-Stack Developer (Laravel + Vue)',
                'company' => 'F1Soft International',
                'location' => 'Lalitpur, Nepal',
                'seniority_level' => 'Mid',
                'raw_description' => 'Develop features for Nepal\'s leading fintech products including e-banking and digital wallet platforms.',
                'posted_at' => now()->subDay(),
                'skills' => [
                    'PHP' => ['required', 4],
                    'Laravel' => ['required', 4],
                    'JavaScript' => ['required', 3],
                    'Vue.js' => ['required', 3],
                    'SQL' => ['required', 3],
                    'API Design' => ['required', 3],
                    'Git' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Flutter Developer',
                'company' => 'Khalti Digital Wallet',
                'location' => 'Kathmandu, Nepal',
                'seniority_level' => 'Mid',
                'raw_description' => 'Build and improve Nepal\'s most-used digital wallet app. Experience with payment integrations and REST APIs preferred.',
                'posted_at' => now()->subDays(3),
                'skills' => [
                    'Flutter' => ['required', 4],
                    'Android Development' => ['required', 3],
                    'API Design' => ['required', 3],
                    'Git' => ['required', 3],
                    'JavaScript' => ['preferred', 3],
                    'Problem Solving' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'DevOps Engineer',
                'company' => 'CloudFactory',
                'location' => 'Kathmandu, Nepal (Hybrid)',
                'seniority_level' => 'Senior',
                'raw_description' => 'Own CI/CD pipelines and cloud infrastructure for AI data-processing workloads. AWS and Kubernetes experience essential.',
                'posted_at' => now()->subDays(7),
                'skills' => [
                    'AWS' => ['required', 4],
                    'Docker' => ['required', 4],
                    'Kubernetes' => ['required', 4],
                    'CI/CD' => ['required', 4],
                    'Git' => ['required', 3],
                    'Networking' => ['preferred', 3],
                    'Python' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Frontend Developer (React)',
                'company' => 'Cotiviti Nepal',
                'location' => 'Kathmandu, Nepal',
                'seniority_level' => 'Entry',
                'raw_description' => 'Create polished healthcare-analytics dashboards with React and TypeScript. Great opportunity for fresh graduates with strong fundamentals.',
                'posted_at' => now()->subDays(6),
                'skills' => [
                    'JavaScript' => ['required', 3],
                    'React' => ['required', 3],
                    'TypeScript' => ['required', 3],
                    'Git' => ['required', 3],
                    'UI/UX Design' => ['preferred', 3],
                    'API Design' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Data Scientist',
                'company' => 'Deerwalk Institute',
                'location' => 'Kathmandu, Nepal',
                'seniority_level' => 'Mid',
                'raw_description' => 'Analyze healthcare data for US clients; build predictive models and dashboards. Healthcare data experience is a plus.',
                'posted_at' => now()->subDays(8),
                'skills' => [
                    'Python' => ['required', 4],
                    'Machine Learning' => ['required', 4],
                    'SQL' => ['required', 4],
                    'Statistics' => ['required', 4],
                    'Data Visualization' => ['preferred', 3],
                    'Microsoft Excel' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'QA Engineer',
                'company' => 'Young Innovations',
                'location' => 'Lalitpur, Nepal',
                'seniority_level' => 'Entry to Mid',
                'raw_description' => 'Perform manual and automated testing for web and mobile products. Freshers with ISTQB certification encouraged to apply.',
                'posted_at' => now()->subDays(9),
                'skills' => [
                    'Quality Assurance' => ['required', 4],
                    'Agile Methodologies' => ['required', 3],
                    'SQL' => ['preferred', 3],
                    'API Design' => ['preferred', 3],
                    'Communication' => ['required', 3],
                ],
            ],
            [
                'title' => 'Digital Marketing Officer',
                'company' => 'Daraz Nepal',
                'location' => 'Kathmandu, Nepal',
                'seniority_level' => 'Entry to Mid',
                'raw_description' => 'Plan and run social media and performance campaigns for Nepal\'s largest e-commerce platform during campaign seasons like Dashain-Tihar sales.',
                'posted_at' => now()->subDays(4),
                'skills' => [
                    'Digital Marketing' => ['required', 4],
                    'Social Media Marketing' => ['required', 4],
                    'SEO' => ['required', 3],
                    'Data Analysis' => ['preferred', 3],
                    'Content Writing' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Junior Accountant',
                'company' => 'NIC Asia Bank',
                'location' => 'Kathmandu, Nepal',
                'seniority_level' => 'Entry',
                'raw_description' => 'Assist in daily bookkeeping, VAT/PAN filings, and reconciliation. Proficiency in Tally ERP and MS Excel required.',
                'posted_at' => now()->subDays(10),
                'skills' => [
                    'Accounting' => ['required', 4],
                    'Tally ERP' => ['required', 4],
                    'Microsoft Excel' => ['required', 4],
                    'Communication' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Customer Service Representative',
                'company' => 'WorldLink Communications',
                'location' => 'Lalitpur, Nepal',
                'seniority_level' => 'Entry',
                'raw_description' => 'Handle customer queries for Nepal\'s largest ISP via phone and chat. Fluent Nepali and English communication required.',
                'posted_at' => now()->subDays(11),
                'skills' => [
                    'Customer Service' => ['required', 4],
                    'Communication' => ['required', 4],
                    'Problem Solving' => ['required', 3],
                    'Microsoft Excel' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Business Data Analyst',
                'company' => 'Nabil Bank',
                'location' => 'Kathmandu, Nepal',
                'seniority_level' => 'Mid',
                'raw_description' => 'Analyze banking operations data and build MIS dashboards for management. Banking domain knowledge preferred.',
                'posted_at' => now()->subDays(12),
                'skills' => [
                    'SQL' => ['required', 4],
                    'Data Analysis' => ['required', 4],
                    'Microsoft Excel' => ['required', 4],
                    'Data Visualization' => ['required', 3],
                    'Statistics' => ['preferred', 3],
                ],
            ],
        ];

        JobPosting::query()->delete();
        JobRequiredSkill::query()->delete();

        foreach ($postings as $posting) {
            $skills = $posting['skills'];
            unset($posting['skills']);

            $model = JobPosting::create($posting);

            foreach ($skills as $skillName => [$importance, $minProficiency]) {
                $skill = Skill::where('name', $skillName)->first();
                if ($skill) {
                    JobRequiredSkill::create([
                        'job_posting_id' => $model->id,
                        'skill_id' => $skill->id,
                        'importance' => $importance,
                        'min_proficiency' => $minProficiency,
                    ]);
                }
            }
        }
    }
}