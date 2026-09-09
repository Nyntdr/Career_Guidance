<?php

namespace Tests\Unit;

use App\Models\CareerRecommendation;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserSkill;
use App\Services\RecommendationEngine;
use App\Support\Settings;
use Database\Seeders\CareerPathSeeder;
use Database\Seeders\CareerRoleSeeder;
use Database\Seeders\SkillAliasSeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecommendationEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed([
            SkillSeeder::class,
            SkillAliasSeeder::class,
            CareerRoleSeeder::class,
            CareerPathSeeder::class,
        ]);
    }

    private function profileWithAllSkillsOf(string $roleTitle): Profile
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([]);

        $role = \App\Models\CareerRole::where('title', $roleTitle)->firstOrFail();

        foreach ($role->requiredSkills as $skill) {
            UserSkill::create([
                'profile_id' => $profile->id,
                'skill_id' => $skill->id,
                'proficiency_level' => 5,
            ]);
        }

        return $profile;
    }

    public function test_generates_recommendations_and_persists_rationale(): void
    {
        $profile = $this->profileWithAllSkillsOf('Data Analyst');

        $created = app(RecommendationEngine::class)->generate($profile);

        $this->assertNotEmpty($created);
        $this->assertSame($created->count(), CareerRecommendation::where('profile_id', $profile->id)->count());
        $this->assertSame($created->count(), CareerRecommendation::where('profile_id', $profile->id)->whereNotNull('rationale')->count());

        $top = $created->first();
        $this->assertSame('Data Analyst', $top->careerRole->title);
        $this->assertGreaterThanOrEqual(70, (float) $top->match_score);
        $this->assertSame('template', $top->rationale_source);
    }

    public function test_only_the_top_recommendation_gets_llm_rationale_when_enabled(): void
    {
        config([
            'recommendations.providers.gemini.endpoint' => 'https://gemini.example/chat/completions',
            'recommendations.providers.gemini.key' => 'test-key',
        ]);
        Settings::save(['llm_rationale' => 'enabled', 'llm_provider' => 'gemini']);

        Http::fake(['*' => Http::response([
            'choices' => [['message' => ['content' => 'AI prose for this role.']]],
        ], 200)]);

        $profile = $this->profileWithAllSkillsOf('Data Analyst');

        $created = app(RecommendationEngine::class)->generate($profile);

        $this->assertNotEmpty($created);
        $this->assertSame('llm', $created->first()->rationale_source);
        $this->assertTrue($created->slice(1)->every(fn ($r) => $r->rationale_source === 'template'));
        $this->assertFalse($created->slice(1)->contains(fn ($r) => $r->rationale_source === 'llm'));

        Http::assertSentCount(1);
    }

    public function test_returns_empty_and_wipes_rows_when_profile_has_no_skills(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([]);

        $result = app(RecommendationEngine::class)->generate($profile);

        $this->assertTrue($result->isEmpty());
        $this->assertSame(0, CareerRecommendation::where('profile_id', $profile->id)->count());
    }

    public function test_regenerating_replaces_previous_rows(): void
    {
        $profile = $this->profileWithAllSkillsOf('Software Engineer');

        app(RecommendationEngine::class)->generate($profile);
        $firstCount = CareerRecommendation::where('profile_id', $profile->id)->count();

        app(RecommendationEngine::class)->generate($profile);
        $secondCount = CareerRecommendation::where('profile_id', $profile->id)->count();

        $this->assertGreaterThan(0, $firstCount);
        $this->assertSame($firstCount, $secondCount);
    }
}