<?php

namespace Tests\Feature;

use App\Models\CareerRecommendation;
use App\Models\CareerRole;
use App\Models\User;
use App\Models\UserSkill;
use App\Services\RecommendationEngine;
use App\Support\Settings;
use Database\Seeders\CareerPathSeeder;
use Database\Seeders\CareerRoleSeeder;
use Database\Seeders\SkillAliasSeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecommendationControllerTest extends TestCase
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

    protected function tearDown(): void
    {
        Settings::flush();

        parent::tearDown();
    }

    private function userWithSkills(string $roleTitle): User
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([]);

        $role = CareerRole::where('title', $roleTitle)->firstOrFail();

        foreach ($role->requiredSkills as $skill) {
            UserSkill::create([
                'profile_id' => $profile->id,
                'skill_id' => $skill->id,
                'proficiency_level' => 5,
            ]);
        }

        return $user;
    }

    public function test_index_requires_authentication(): void
    {
        $this->get(route('recommendations.index'))->assertRedirect('/login');
    }

    public function test_index_renders_generated_recommendations(): void
    {
        $user = $this->userWithSkills('Data Analyst');

        $response = $this->actingAs($user)->get(route('recommendations.index'));

        $response->assertOk();

        $recommendations = $response->inertiaProps('recommendations');
        $this->assertNotEmpty($recommendations);
        $this->assertEquals('Data Analyst', $recommendations[0]['title']);
        $this->assertNotEmpty($recommendations[0]['rationale']);
        $this->assertArrayHasKey('matched_skills', $recommendations[0]);
        $this->assertArrayHasKey('rationale_source', $recommendations[0]);
    }

    public function test_index_shows_empty_state_when_no_skills(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([]);

        $response = $this->actingAs($user)->get(route('recommendations.index'));

        $response->assertOk();
        $this->assertEmpty($response->inertiaProps('recommendations'));
    }

    public function test_generate_creates_recommendations(): void
    {
        $user = $this->userWithSkills('Data Analyst');

        $response = $this->actingAs($user)->from(route('dashboard'))
            ->post(route('recommendations.generate'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
    }

    public function test_rationale_updates_owned_recommendation(): void
    {
        $user = $this->userWithSkills('Data Analyst');
        $profile = $user->profile()->firstOrFail();
        app(RecommendationEngine::class)->generate($profile);

        $rec = CareerRecommendation::where('profile_id', $profile->id)->firstOrFail();

        $response = $this->actingAs($user)->from(route('recommendations.index'))
            ->post(route('recommendations.rationale', $rec));

        $response->assertRedirect(route('recommendations.index'));

        $rec->refresh();
        $this->assertNotEmpty($rec->rationale);
        $this->assertSame('template', $rec->rationale_source);
    }

    public function test_rationale_is_forbidden_for_another_users_recommendation(): void
    {
        $owner = $this->userWithSkills('Data Analyst');
        $profile = $owner->profile()->firstOrFail();
        app(RecommendationEngine::class)->generate($profile);

        $rec = CareerRecommendation::where('profile_id', $profile->id)->firstOrFail();

        $other = User::factory()->create();
        $other->profile()->create([]);

        $this->actingAs($other)->post(route('recommendations.rationale', $rec))
            ->assertForbidden();
    }

    public function test_rationale_for_second_best_recommendation_calls_provider_once(): void
    {
        $user = $this->userWithSkills('Data Analyst');
        $profile = $user->profile()->firstOrFail();
        app(RecommendationEngine::class)->generate($profile);

        $this->enableGemini();

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'choices' => [['message' => ['content' => 'A realistic next step. Build on your SQL and statistics skills.']]],
            ], 200),
        ]);

        $top = CareerRecommendation::where('profile_id', $profile->id)
            ->orderByDesc('match_score')->orderBy('id')->firstOrFail();

        $second = CareerRecommendation::where('profile_id', $profile->id)
            ->where('id', '!=', $top->id)
            ->orderByDesc('match_score')->orderBy('id')->firstOrFail();

        $response = $this->actingAs($user)->from(route('recommendations.index'))
            ->post(route('recommendations.rationale', $second));

        $response->assertRedirect(route('recommendations.index'));
        $response->assertSessionHas('success');

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://generativelanguage.googleapis.com/'));
        $this->assertSame('llm', $second->fresh()->rationale_source);
    }

    public function test_rationale_for_top_recommendation_calls_provider_once(): void
    {
        $user = $this->userWithSkills('Data Analyst');
        $profile = $user->profile()->firstOrFail();
        app(RecommendationEngine::class)->generate($profile);

        $this->enableGemini();

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'choices' => [['message' => ['content' => 'A strong fit. Focus on Statistics next.']]],
            ], 200),
        ]);

        $top = CareerRecommendation::where('profile_id', $profile->id)
            ->orderByDesc('match_score')->orderBy('id')->firstOrFail();

        $this->actingAs($user)->from(route('recommendations.index'))
            ->post(route('recommendations.rationale', $top));

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://generativelanguage.googleapis.com/'));
        $this->assertSame('llm', $top->fresh()->rationale_source);
    }

    public function test_concurrent_rationale_request_is_skipped_while_lock_held(): void
    {
        $user = $this->userWithSkills('Data Analyst');
        $profile = $user->profile()->firstOrFail();
        app(RecommendationEngine::class)->generate($profile);

        $this->enableGemini();

        Http::fake();

        $lock = Cache::lock('rationalize:'.$profile->id, 10);
        $this->assertTrue($lock->get());

        $top = CareerRecommendation::where('profile_id', $profile->id)
            ->orderByDesc('match_score')->orderBy('id')->firstOrFail();

        $this->actingAs($user)->from(route('recommendations.index'))
            ->post(route('recommendations.rationale', $top));

        Http::assertNothingSent();
        $this->assertSame('template', $top->fresh()->rationale_source);
    }

    public function test_dashboard_auto_generation_is_skipped_while_lock_held(): void
    {
        $user = $this->userWithSkills('Data Analyst');
        $profile = $user->profile()->firstOrFail();

        $this->enableGemini();

        Http::fake();

        $lock = Cache::lock('rec-generate:'.$profile->id, 120);
        $this->assertTrue($lock->get());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        Http::assertNothingSent();
        $this->assertDatabaseCount('career_recommendations', 0);
    }

    private function enableGemini(): void
    {
        config([
            'recommendations.providers.gemini.endpoint' => 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions',
            'recommendations.providers.gemini.key' => 'gemini-key',
        ]);

        Settings::save(['llm_rationale' => 'enabled', 'llm_provider' => 'gemini']);
    }
}
