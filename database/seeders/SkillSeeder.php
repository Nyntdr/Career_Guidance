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
            'Backend', 'Mobile', 'Cloud', 'DevOps', 'Tools',
            'Process', 'Management', 'Soft Skills',
            'Marketing', 'Finance', 'Design', 'Healthcare',
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
            ['name' => 'Flutter', 'category' => 'Mobile'],
            ['name' => 'Android Development', 'category' => 'Mobile'],
            ['name' => 'React Native', 'category' => 'Mobile'],
            ['name' => 'Digital Marketing', 'category' => 'Marketing'],
            ['name' => 'SEO', 'category' => 'Marketing'],
            ['name' => 'Social Media Marketing', 'category' => 'Marketing'],
            ['name' => 'Accounting', 'category' => 'Finance'],
            ['name' => 'Tally ERP', 'category' => 'Finance'],
            ['name' => 'Microsoft Excel', 'category' => 'Tools'],
            ['name' => 'Network Security', 'category' => 'Cloud'],
            ['name' => 'Networking', 'category' => 'Cloud'],
            ['name' => 'Graphic Design', 'category' => 'Design'],
            ['name' => 'UI/UX Design', 'category' => 'Design'],
            ['name' => 'Figma', 'category' => 'Design'],
            ['name' => 'Adobe Photoshop', 'category' => 'Design'],
            ['name' => 'Content Writing', 'category' => 'Marketing'],
            ['name' => 'Customer Service', 'category' => 'Soft Skills'],
            ['name' => 'Quality Assurance', 'category' => 'Process'],
            ['name' => 'AutoCAD', 'category' => 'Design'],
            ['name' => 'Surveying', 'category' => 'Design'],
            ['name' => 'Patient Care', 'category' => 'Healthcare'],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(
                ['name' => $skill['name']],
                ['category_id' => $categoryMap[$skill['category']]],
            );
        }
    }
}
