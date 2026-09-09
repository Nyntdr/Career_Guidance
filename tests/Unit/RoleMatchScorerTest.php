<?php

namespace Tests\Unit;

use App\Models\CareerRole;
use App\Services\RoleMatchScorer;
use Database\Seeders\CareerRoleSeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMatchScorerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SkillSeeder::class, CareerRoleSeeder::class]);
    }

    public function test_full_overlap_reaches_maximum_score(): void
    {
        $role = CareerRole::where('title', 'Data Analyst')->firstOrFail();

        $map = collect();

        foreach ($role->requiredSkills as $skill) {
            $proficiency = $skill->pivot->importance === 'essential'
                ? max($skill->pivot->min_proficiency, 1)
                : 5;
            $map->put($skill->id, (object) ['skill_id' => $skill->id, 'proficiency_level' => $proficiency]);
        }

        $result = app(RoleMatchScorer::class)->overlap($map, $role);

        $this->assertSame(100, $result['score']);
        $this->assertEmpty($result['missing']);
    }

    public function test_scores_essential_skills_and_reports_missing(): void
    {
        $role = CareerRole::where('title', 'Data Scientist')->firstOrFail();

        $essential = $role->requiredSkills->firstWhere('pivot.importance', 'essential');
        $this->assertNotNull($essential, 'Expected at least one essential skill on Data Scientist');

        $partial = collect();

        foreach ($role->requiredSkills as $skill) {
            if ($skill->id === $essential->id) {
                continue; // omit one essential skill entirely
            }

            $proficiency = $skill->pivot->importance === 'essential'
                ? max($skill->pivot->min_proficiency, 1)
                : 5;
            $partial->put($skill->id, (object) ['skill_id' => $skill->id, 'proficiency_level' => $proficiency]);
        }

        $result = app(RoleMatchScorer::class)->overlap($partial, $role);

        $this->assertLessThan(100, $result['score']);
        $this->assertNotEmpty($result['missing']);
        $this->assertContains($essential->id, $result['missing']);
    }

    public function test_below_minimum_proficiency_counts_as_unmatched(): void
    {
        $role = CareerRole::where('title', 'Backend Developer')->firstOrFail();

        $essential = $role->requiredSkills->firstWhere('pivot.importance', 'essential');
        $this->assertNotNull($essential, 'Expected an essential skill on Backend Developer');

        $map = collect();

        foreach ($role->requiredSkills as $skill) {
            $map->put($skill->id, (object) ['skill_id' => $skill->id, 'proficiency_level' => 1]);
        }

        $result = app(RoleMatchScorer::class)->overlap($map, $role);

        if ((int) $essential->pivot->min_proficiency > 1) {
            $this->assertContains($essential->id, $result['missing']);
        }
    }
}