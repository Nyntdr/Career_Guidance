<?php

namespace Database\Seeders;

use App\Models\CareerPath;
use App\Models\CareerRole;
use Illuminate\Database\Seeder;

class CareerPathSeeder extends Seeder
{
    protected array $paths = [
        // Tech / IT paths
        ['from' => 'Data Analyst', 'to' => 'Data Scientist', 'typical_years' => 2, 'notes' => 'Deepen statistics and Python skills, move into predictive modeling.'],
        ['from' => 'Data Analyst', 'to' => 'Product Manager', 'typical_years' => 3, 'notes' => 'Leverage business insight and stakeholder communication to own product decisions.'],
        ['from' => 'Data Scientist', 'to' => 'Machine Learning Engineer', 'typical_years' => 2, 'notes' => 'Focus on productionizing models, pipelines, and deployed ML systems.'],
        ['from' => 'Machine Learning Engineer', 'to' => 'Data Scientist', 'typical_years' => 2, 'notes' => 'Shift toward experimentation, research, and advanced statistical methods.'],
        ['from' => 'Software Engineer', 'to' => 'DevOps Engineer', 'typical_years' => 2, 'notes' => 'Move into infrastructure, CI/CD, and cloud automation.'],
        ['from' => 'Software Engineer', 'to' => 'Machine Learning Engineer', 'typical_years' => 3, 'notes' => 'Add ML modeling depth to strong software engineering fundamentals.'],
        ['from' => 'Frontend Developer', 'to' => 'Software Engineer', 'typical_years' => 2, 'notes' => 'Broaden into full-stack engineering with backend and architecture skills.'],
        ['from' => 'Backend Developer', 'to' => 'Software Engineer', 'typical_years' => 2, 'notes' => 'Round out frontend and system design to become a full-stack engineer.'],
        ['from' => 'Software Engineer', 'to' => 'Backend Developer', 'typical_years' => 1, 'notes' => 'Specialize in server-side logic, databases, and API design.'],
        ['from' => 'Software Engineer', 'to' => 'Frontend Developer', 'typical_years' => 1, 'notes' => 'Specialize in frameworks, UI engineering, and web performance.'],
        ['from' => 'Product Manager', 'to' => 'Data Scientist', 'typical_years' => 4, 'notes' => 'Complement delivery leadership with deep quantitative analysis.'],
        ['from' => 'Frontend Developer', 'to' => 'Mobile App Developer', 'typical_years' => 1, 'notes' => 'Transfer UI skills to Flutter or React Native — a fast-growing niche in Nepal.'],
        ['from' => 'Mobile App Developer', 'to' => 'Software Engineer', 'typical_years' => 2, 'notes' => 'Broaden into web and backend systems for full-stack versatility.'],
        ['from' => 'Frontend Developer', 'to' => 'UI/UX Designer', 'typical_years' => 2, 'notes' => 'Move from building interfaces to designing them with user research skills.'],
        ['from' => 'UI/UX Designer', 'to' => 'Product Manager', 'typical_years' => 3, 'notes' => 'Turn design thinking and user empathy into product ownership.'],
        ['from' => 'Graphic Designer', 'to' => 'UI/UX Designer', 'typical_years' => 2, 'notes' => 'Learn Figma, prototyping, and usability to move from visuals to product design.'],
        ['from' => 'QA Engineer', 'to' => 'Software Engineer', 'typical_years' => 2, 'notes' => 'Use testing knowledge of codebases to transition into development.'],
        ['from' => 'QA Engineer', 'to' => 'DevOps Engineer', 'typical_years' => 3, 'notes' => 'Extend test automation into CI/CD pipelines and infrastructure.'],
        ['from' => 'Backend Developer', 'to' => 'DevOps Engineer', 'typical_years' => 2, 'notes' => 'Leverage server-side experience for infrastructure and deployment roles.'],
        ['from' => 'Digital Marketing Specialist', 'to' => 'Product Manager', 'typical_years' => 3, 'notes' => 'Apply market insight and customer understanding to product strategy.'],
        ['from' => 'Content Writer', 'to' => 'Digital Marketing Specialist', 'typical_years' => 1, 'notes' => 'Extend writing skills into SEO strategy, ads, and campaign management.'],
        ['from' => 'Customer Service Representative', 'to' => 'Digital Marketing Specialist', 'typical_years' => 2, 'notes' => 'Use customer insight and communication skills in brand and social media roles.'],

        // Non-tech paths
        ['from' => 'Accountant', 'to' => 'Bank Officer', 'typical_years' => 2, 'notes' => 'Move into banking credit or operations — accounting is a natural feeder role in Nepal.'],
        ['from' => 'Bank Officer', 'to' => 'Accountant', 'typical_years' => 2, 'notes' => 'Transition into corporate finance roles in businesses or NGOs.'],
        ['from' => 'Accountant', 'to' => 'Data Analyst', 'typical_years' => 2, 'notes' => 'Build on Excel and reporting skills with SQL and data visualization.'],
        ['from' => 'Bank Officer', 'to' => 'Data Analyst', 'typical_years' => 3, 'notes' => 'Banks value domain knowledge combined with data skills for analytics teams.'],
        ['from' => 'Network Engineer', 'to' => 'DevOps Engineer', 'typical_years' => 2, 'notes' => 'Pair networking depth with cloud and automation skills.'],
        ['from' => 'Customer Service Representative', 'to' => 'Bank Officer', 'typical_years' => 2, 'notes' => 'Client-facing experience is valued for bank teller and relationship roles.'],
        ['from' => 'UI/UX Designer', 'to' => 'Frontend Developer', 'typical_years' => 2, 'notes' => 'Learn HTML/CSS/JS to implement your own designs.'],
    ];

    public function run(): void
    {
        foreach ($this->paths as $path) {
            $fromRole = CareerRole::where('title', $path['from'])->first();
            $toRole = CareerRole::where('title', $path['to'])->first();

            if (!$fromRole || !$toRole) {
                continue;
            }

            CareerPath::firstOrCreate(
                ['from_role_id' => $fromRole->id, 'to_role_id' => $toRole->id],
                [
                    'typical_years' => $path['typical_years'],
                    'notes' => $path['notes'],
                ],
            );
        }
    }
}