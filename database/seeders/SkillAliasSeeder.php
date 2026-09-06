<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\SkillAlias;
use Illuminate\Database\Seeder;

class SkillAliasSeeder extends Seeder
{
    protected array $aliases = [
        'Machine Learning' => ['ML'],
        'Artificial Intelligence' => ['AI'],
        'Natural Language Processing' => ['NLP'],
        'JavaScript' => ['JS'],
        'TypeScript' => ['TS'],
        'React' => ['ReactJS', 'React.js'],
        'Vue.js' => ['Vue', 'VueJS', 'Vue.js'],
        'Node.js' => ['Node', 'NodeJS'],
        'Kubernetes' => ['K8s'],
        'API Design' => ['APIs', 'API', 'REST', 'REST API', 'REST APIs'],
        'Agile Methodologies' => ['Agile', 'Scrum'],
        'Project Management' => ['PM', 'Projectmanager'],
        'Git' => ['Version control', 'Version Control'],
        'Data Visualization' => ['Dashboarding', 'Data Viz'],
        'SQL' => ['MySQL', 'PostgreSQL', 'Postgres'],
    ];

    public function run(): void
    {
        foreach ($this->aliases as $skillName => $aliasList) {
            $skill = Skill::where('name', $skillName)->first();
            if (!$skill) {
                continue;
            }

            foreach ($aliasList as $alias) {
                SkillAlias::firstOrCreate([
                    'skill_id' => $skill->id,
                    'alias' => $alias,
                ]);
            }
        }
    }
}