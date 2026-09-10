<?php

namespace Tests\Unit;

use App\Models\CareerRole;
use App\Services\Ml\SkillSimilarity;
use Database\Seeders\CareerRoleSeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillSimilarityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SkillSeeder::class, CareerRoleSeeder::class]);
    }

    public function test_returns_zero_for_a_role_with_no_requirements(): void
    {
        $role = CareerRole::create(['title' => 'Empty role']);

        $this->assertSame(0.0, app(SkillSimilarity::class)->score(collect(), $role, collect([$role])));
    }

    public function test_full_similarity_for_exact_match(): void
    {
        $role = CareerRole::where('title', 'Data Scientist')->firstOrFail();
        $userSkillMap = $role->requiredSkills->mapWithKeys(fn ($skill) => [
            $skill->id => (object) ['skill_id' => $skill->id, 'proficiency_level' => 5],
        ]);

        $score = app(SkillSimilarity::class)->score($userSkillMap, $role, collect([$role]));

        $this->assertGreaterThan(90, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_scoring_is_higher_for_more_similar_roles(): void
    {
        $dataScientist = CareerRole::where('title', 'Data Scientist')->firstOrFail();
        $mlEngineer = CareerRole::where('title', 'Machine Learning Engineer')->firstOrFail();
        $frontend = CareerRole::where('title', 'Frontend Developer')->firstOrFail();

        $userSkillMap = $dataScientist->requiredSkills->mapWithKeys(fn ($skill) => [
            $skill->id => (object) ['skill_id' => $skill->id, 'proficiency_level' => 5],
        ]);

        $service = app(SkillSimilarity::class);
        $allRoles = CareerRole::with('requiredSkills')->get();

        $core = $service->score($userSkillMap, $dataScientist, $allRoles);
        $related = $service->score($userSkillMap, $mlEngineer, $allRoles);
        $unrelated = $service->score($userSkillMap, $frontend, $allRoles);

        $this->assertGreaterThan($related, $core);
        $this->assertGreaterThan($unrelated, $related);
    }
}