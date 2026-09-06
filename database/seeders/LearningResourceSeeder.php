<?php

namespace Database\Seeders;

use App\Models\LearningResource;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class LearningResourceSeeder extends Seeder
{
    public function run(): void
    {
        $resources = [
            [
                'title' => 'PHP for Beginners',
                'provider' => 'Laracasts',
                'url' => 'https://laracasts.com/series/php-for-beginners',
                'resource_type' => 'course',
                'duration_hours' => 10,
                'cost' => 0,
                'skills' => ['PHP'],
            ],
            [
                'title' => 'Laravel Bootcamp',
                'provider' => 'Laravel',
                'url' => 'https://bootcamp.laravel.com/',
                'resource_type' => 'course',
                'duration_hours' => 12,
                'cost' => 0,
                'skills' => ['Laravel', 'PHP'],
            ],
            [
                'title' => 'Full-Stack React with TypeScript',
                'provider' => 'Udemy',
                'url' => 'https://www.udemy.com/',
                'resource_type' => 'course',
                'duration_hours' => 25,
                'cost' => 49.99,
                'skills' => ['React', 'TypeScript', 'JavaScript'],
            ],
            [
                'title' => 'Machine Learning Specialization',
                'provider' => 'Coursera',
                'url' => 'https://www.coursera.org/',
                'resource_type' => 'course',
                'duration_hours' => 40,
                'cost' => 0,
                'skills' => ['Machine Learning', 'Python', 'Statistics'],
            ],
            [
                'title' => 'Deep Learning Fundamentals',
                'provider' => 'DeepLearning.AI',
                'url' => 'https://www.deeplearning.ai/',
                'resource_type' => 'course',
                'duration_hours' => 20,
                'cost' => 49,
                'skills' => ['Deep Learning', 'Machine Learning', 'Python'],
            ],
            [
                'title' => 'The Python Tutorial',
                'provider' => 'Python.org',
                'url' => 'https://docs.python.org/3/tutorial/',
                'resource_type' => 'article',
                'duration_hours' => 6,
                'cost' => 0,
                'skills' => ['Python'],
            ],
            [
                'title' => 'Docker for Developers',
                'provider' => 'Dev.to',
                'url' => 'https://dev.to/t/docker',
                'resource_type' => 'article',
                'duration_hours' => 3,
                'cost' => 0,
                'skills' => ['Docker'],
            ],
            [
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'provider' => 'Robert C. Martin',
                'url' => null,
                'resource_type' => 'book',
                'duration_hours' => 8,
                'cost' => 35,
                'skills' => ['Problem Solving'],
            ],
            [
                'title' => 'Data Visualization: A Practical Introduction',
                'provider' => 'Kieran Healy',
                'url' => 'https://socviz.co/',
                'resource_type' => 'book',
                'duration_hours' => 10,
                'cost' => 0,
                'skills' => ['Data Visualization', 'Data Analysis'],
            ],
            [
                'title' => 'Intro to Computer Vision with Python',
                'provider' => 'YouTube',
                'url' => 'https://www.youtube.com/results?search_query=computer+vision+python',
                'resource_type' => 'video',
                'duration_hours' => 5,
                'cost' => 0,
                'skills' => ['Computer Vision', 'Python'],
            ],
            [
                'title' => 'AWS Certified Solutions Architect',
                'provider' => 'AWS',
                'url' => 'https://aws.amazon.com/certification/',
                'resource_type' => 'certification',
                'duration_hours' => 35,
                'cost' => 150,
                'skills' => ['AWS', 'CI/CD'],
            ],
            [
                'title' => 'Google Cloud Associate Cloud Engineer',
                'provider' => 'Google Cloud',
                'url' => 'https://cloud.google.com/learn/certification',
                'resource_type' => 'certification',
                'duration_hours' => 40,
                'cost' => 125,
                'skills' => ['AWS', 'Docker', 'Kubernetes'],
            ],
            [
                'title' => 'Scrum Master Certification Prep',
                'provider' => 'Scrum.org',
                'url' => 'https://www.scrum.org/',
                'resource_type' => 'certification',
                'duration_hours' => 15,
                'cost' => 0,
                'skills' => ['Agile Methodologies', 'Project Management'],
            ],
            [
                'title' => 'NLP with Transformers',
                'provider' => 'Hugging Face',
                'url' => 'https://huggingface.co/learn/nlp-course',
                'resource_type' => 'course',
                'duration_hours' => 14,
                'cost' => 0,
                'skills' => ['Natural Language Processing', 'Python'],
            ],
            [
                'title' => 'Effective Communication for Engineers',
                'provider' => 'LinkedIn Learning',
                'url' => 'https://www.linkedin.com/learning/',
                'resource_type' => 'other',
                'duration_hours' => 4,
                'cost' => 29.99,
                'skills' => ['Communication', 'Critical Thinking'],
            ],
            [
                'title' => 'SQL for Data Analysis',
                'provider' => 'Kaggle',
                'url' => 'https://www.kaggle.com/learn/',
                'resource_type' => 'course',
                'duration_hours' => 5,
                'cost' => 0,
                'skills' => ['SQL', 'Data Analysis'],
            ],
            [
                'title' => 'Introduction to R Programming',
                'provider' => 'DataCamp',
                'url' => 'https://www.datacamp.com/',
                'resource_type' => 'course',
                'duration_hours' => 8,
                'cost' => 0,
                'skills' => ['R Programming', 'Statistics'],
            ],
            [
                'title' => 'Node.js Event Loop Explained',
                'provider' => 'YouTube',
                'url' => 'https://www.youtube.com/results?search_query=nodejs+event+loop',
                'resource_type' => 'video',
                'duration_hours' => 2,
                'cost' => 0,
                'skills' => ['Node.js', 'JavaScript'],
            ],
        ];

        foreach ($resources as $resource) {
            $skills = $resource['skills'];
            unset($resource['skills']);

            $model = LearningResource::updateOrCreate(
                ['title' => $resource['title']],
                $resource,
            );

            $skillIds = Skill::whereIn('name', $skills)->pluck('id');
            $model->skills()->sync($skillIds);
        }
    }
}