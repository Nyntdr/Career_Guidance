<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Programming', 'Data', 'AI/ML', 'Frontend',
            'Backend', 'Cloud', 'DevOps', 'Tools',
            'Process', 'Management', 'Soft Skills',
        ];

        $categoryMap = [];
        foreach ($categories as $name) {
            $categoryMap[$name] = SkillCategory::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)],
            )->id;
        }

        $skills = [
            ['name' => 'Python', 'category' => 'Programming'],
            ['name' => 'SQL', 'category' => 'Data'],
            ['name' => 'Machine Learning', 'category' => 'AI/ML'],
            ['name' => 'Statistics', 'category' => 'Data'],
            ['name' => 'Data Visualization', 'category' => 'Data'],
            ['name' => 'Deep Learning', 'category' => 'AI/ML'],
            ['name' => 'Natural Language Processing', 'category' => 'AI/ML'],
            ['name' => 'Computer Vision', 'category' => 'AI/ML'],
            ['name' => 'R Programming', 'category' => 'Programming'],
            ['name' => 'Java', 'category' => 'Programming'],
            ['name' => 'JavaScript', 'category' => 'Programming'],
            ['name' => 'TypeScript', 'category' => 'Programming'],
            ['name' => 'React', 'category' => 'Frontend'],
            ['name' => 'Vue.js', 'category' => 'Frontend'],
            ['name' => 'Node.js', 'category' => 'Backend'],
            ['name' => 'PHP', 'category' => 'Programming'],
            ['name' => 'Laravel', 'category' => 'Backend'],
            ['name' => 'AWS', 'category' => 'Cloud'],
            ['name' => 'Docker', 'category' => 'DevOps'],
            ['name' => 'Kubernetes', 'category' => 'DevOps'],
            ['name' => 'Git', 'category' => 'Tools'],
            ['name' => 'CI/CD', 'category' => 'DevOps'],
            ['name' => 'Agile Methodologies', 'category' => 'Process'],
            ['name' => 'Project Management', 'category' => 'Management'],
            ['name' => 'Communication', 'category' => 'Soft Skills'],
            ['name' => 'Leadership', 'category' => 'Soft Skills'],
            ['name' => 'Critical Thinking', 'category' => 'Soft Skills'],
            ['name' => 'Problem Solving', 'category' => 'Soft Skills'],
            ['name' => 'Data Analysis', 'category' => 'Data'],
            ['name' => 'API Design', 'category' => 'Backend'],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(
                ['name' => $skill['name']],
                ['category_id' => $categoryMap[$skill['category']]],
            );
        }
    }
}
