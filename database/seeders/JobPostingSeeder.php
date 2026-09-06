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
                'title' => 'Senior Data Scientist',
                'company' => 'Nova Analytics',
                'location' => 'Remote',
                'seniority_level' => 'Senior',
                'raw_description' => 'Build ML models and drive data-driven decisions across the product.',
                'posted_at' => now()->subDays(2),
                'skills' => [
                    'Python' => ['required', 4],
                    'Machine Learning' => ['required', 4],
                    'SQL' => ['required', 3],
                    'Statistics' => ['required', 4],
                    'Data Visualization' => ['preferred', 3],
                    'Deep Learning' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Machine Learning Engineer',
                'company' => 'TensorFlow Labs',
                'location' => 'San Francisco, CA',
                'seniority_level' => 'Senior',
                'raw_description' => 'Design and deploy production-grade ML pipelines at scale.',
                'posted_at' => now()->subDays(5),
                'skills' => [
                    'Python' => ['required', 4],
                    'Machine Learning' => ['required', 4],
                    'Docker' => ['required', 3],
                    'AWS' => ['required', 3],
                    'Deep Learning' => ['required', 4],
                    'CI/CD' => ['preferred', 3],
                    'Git' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Full-Stack Developer',
                'company' => 'Brightworks',
                'location' => 'Berlin, DE',
                'seniority_level' => 'Mid',
                'raw_description' => 'Build web applications across the stack with modern frameworks.',
                'posted_at' => now()->subDay(),
                'skills' => [
                    'JavaScript' => ['required', 3],
                    'React' => ['required', 4],
                    'TypeScript' => ['required', 3],
                    'API Design' => ['required', 3],
                    'Node.js' => ['preferred', 3],
                    'Git' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Backend Engineer (Laravel)',
                'company' => 'Craft Systems',
                'location' => 'Remote',
                'seniority_level' => 'Mid',
                'raw_description' => 'Build RESTful APIs and services with PHP and Laravel.',
                'posted_at' => now()->subDays(3),
                'skills' => [
                    'PHP' => ['required', 4],
                    'Laravel' => ['required', 4],
                    'SQL' => ['required', 3],
                    'API Design' => ['required', 4],
                    'Docker' => ['preferred', 3],
                    'Git' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'DevOps Engineer',
                'company' => 'CloudPulse',
                'location' => 'Austin, TX',
                'seniority_level' => 'Senior',
                'raw_description' => 'Own CI/CD pipelines and cloud infrastructure reliability.',
                'posted_at' => now()->subDays(7),
                'skills' => [
                    'AWS' => ['required', 4],
                    'Docker' => ['required', 4],
                    'Kubernetes' => ['required', 4],
                    'CI/CD' => ['required', 4],
                    'Git' => ['required', 3],
                    'Python' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Frontend Developer (Vue)',
                'company' => 'PixelWorks',
                'location' => 'Remote',
                'seniority_level' => 'Entry',
                'raw_description' => 'Create polished user interfaces with Vue and modern tooling.',
                'posted_at' => now()->subDays(6),
                'skills' => [
                    'JavaScript' => ['required', 3],
                    'Vue.js' => ['required', 3],
                    'TypeScript' => ['required', 3],
                    'API Design' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Product Manager',
                'company' => 'Atlas Product Co',
                'location' => 'New York, NY',
                'seniority_level' => 'Lead',
                'raw_description' => 'Own product strategy and roadmap across cross-functional teams.',
                'posted_at' => now()->subDays(9),
                'skills' => [
                    'Project Management' => ['required', 4],
                    'Agile Methodologies' => ['required', 4],
                    'Communication' => ['required', 4],
                    'Leadership' => ['required', 3],
                    'Data Analysis' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Data Analyst',
                'company' => 'Insight Metrics',
                'location' => 'Chicago, IL',
                'seniority_level' => 'Entry',
                'raw_description' => 'Turn raw data into clear reports and dashboards for stakeholders.',
                'posted_at' => now()->subDays(4),
                'skills' => [
                    'SQL' => ['required', 4],
                    'Data Analysis' => ['required', 3],
                    'Data Visualization' => ['required', 3],
                    'Statistics' => ['preferred', 3],
                    'Python' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Computer Vision Engineer',
                'company' => 'Visionary AI',
                'location' => 'Toronto, CA',
                'seniority_level' => 'Mid',
                'raw_description' => 'Develop computer vision models for real-time perception systems.',
                'posted_at' => now()->subDays(8),
                'skills' => [
                    'Python' => ['required', 4],
                    'Computer Vision' => ['required', 4],
                    'Deep Learning' => ['required', 4],
                    'Machine Learning' => ['required', 4],
                ],
            ],
            [
                'title' => 'NLP Engineer',
                'company' => 'LinguaTech',
                'location' => 'Remote',
                'seniority_level' => 'Senior',
                'raw_description' => 'Build NLP systems powering search, chat, and document understanding.',
                'posted_at' => now()->subDays(10),
                'skills' => [
                    'Python' => ['required', 4],
                    'Natural Language Processing' => ['required', 4],
                    'Machine Learning' => ['required', 4],
                    'Deep Learning' => ['preferred', 4],
                ],
            ],
            [
                'title' => 'Junior Software Engineer',
                'company' => 'Greenfield Labs',
                'location' => 'Remote',
                'seniority_level' => 'Entry',
                'raw_description' => 'Start your career building features in a supportive engineering team.',
                'posted_at' => now()->subDays(11),
                'skills' => [
                    'JavaScript' => ['required', 3],
                    'Git' => ['required', 3],
                    'Problem Solving' => ['required', 3],
                    'SQL' => ['preferred', 3],
                ],
            ],
            [
                'title' => 'Technical Project Manager',
                'company' => 'Vector Systems',
                'location' => 'London, UK',
                'seniority_level' => 'Mid',
                'raw_description' => 'Coordinate engineering delivery with stakeholders.',
                'posted_at' => now()->subDays(12),
                'skills' => [
                    'Project Management' => ['required', 3],
                    'Agile Methodologies' => ['required', 4],
                    'Communication' => ['required', 4],
                    'Leadership' => ['preferred', 3],
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