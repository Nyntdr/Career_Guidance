<?php

namespace Tests\Unit;

use App\Models\CareerRole;
use App\Models\Profile;
use App\Models\User;
use App\Services\RecommendationRationalizer;
use App\Support\Settings;
use Database\Seeders\CareerRoleSeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecommendationRationalizerTest extends TestCase
{
    use RefreshDatabase;

    private array $original;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SkillSeeder::class, CareerRoleSeeder::class]);

        $this->original = config('recommendations.providers');
        Settings::flush();
    }

    protected function tearDown(): void
    {
        config(['recommendations.providers' => $this->original]);
        Settings::flush();

        parent::tearDown();
    }

    private function buildContext(string $roleTitle): array
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([]);
        $role = CareerRole::where('title', $roleTitle)->firstOrFail();

        $overlap = [
            'score' => 80,
            'matched' => $role->requiredSkills->take(3)->pluck('id')->all(),
            'missing' => $role->requiredSkills->take(2)->pluck('id')->all(),
        ];

        return [$profile, $role, $overlap];
    }

    public function test_uses_template_when_setting_is_disabled_even_with_a_key(): void
    {
        config(['recommendations.providers.gemini.key' => 'secret', 'recommendations.providers.gemini.endpoint' => 'https://gemini.example/v1/chat/completions']);
        Settings::save(['llm_rationale' => 'disabled', 'llm_provider' => 'gemini']);

        Http::fake();

        [$profile, $role, $overlap] = $this->buildContext('Data Analyst');
        $result = app(RecommendationRationalizer::class)->for($profile, $role, $overlap);

        $this->assertSame('template', $result['source']);
        $this->assertStringContainsString('align well with this role', $result['text']);
        Http::assertNothingSent();
    }

    public function test_uses_template_when_no_key_is_configured_even_if_enabled(): void
    {
        config(['recommendations.providers.gemini.key' => '', 'recommendations.providers.gemini.endpoint' => 'https://gemini.example/v1/chat/completions']);
        Settings::save(['llm_rationale' => 'enabled', 'llm_provider' => 'gemini']);

        Http::fake();

        [$profile, $role, $overlap] = $this->buildContext('Data Analyst');
        $result = app(RecommendationRationalizer::class)->for($profile, $role, $overlap);

        $this->assertSame('template', $result['source']);
        Http::assertNothingSent();
    }

    public function test_uses_template_when_no_endpoint_is_configured(): void
    {
        config(['recommendations.providers.gemini.key' => 'secret', 'recommendations.providers.gemini.endpoint' => '']);
        Settings::save(['llm_rationale' => 'enabled', 'llm_provider' => 'gemini']);

        Http::fake();

        [$profile, $role, $overlap] = $this->buildContext('Data Analyst');
        $result = app(RecommendationRationalizer::class)->for($profile, $role, $overlap);

        $this->assertSame('template', $result['source']);
        Http::assertNothingSent();
    }

    public function test_calls_the_configured_endpoint_and_returns_llm_prose_when_enabled(): void
    {
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions';

        config([
            'recommendations.providers.gemini.endpoint' => $endpoint,
            'recommendations.providers.gemini.key' => 'gemini-key',
            'recommendations.providers.gemini.model' => 'gemini-3.6-flash',
        ]);
        Settings::save(['llm_rationale' => 'enabled', 'llm_provider' => 'gemini']);

        Http::fake([
            $endpoint => Http::response([
                'choices' => [
                    ['message' => ['content' => 'A strong fit. Focus on Statistics next.']],
                ],
            ], 200),
        ]);

        [$profile, $role, $overlap] = $this->buildContext('Data Analyst');
        $result = app(RecommendationRationalizer::class)->for($profile, $role, $overlap);

        $this->assertSame('llm', $result['source']);
        $this->assertSame('A strong fit. Focus on Statistics next.', $result['text']);

        Http::assertSent(function ($request) use ($endpoint) {
            return $request->url() === $endpoint
                && $request->hasHeader('Authorization', 'Bearer gemini-key')
                && $request['model'] === 'gemini-3.6-flash'
                && is_array($request['messages']);
        });
    }

    public function test_silently_falls_back_to_template_when_provider_fails(): void
    {
        config([
            'recommendations.providers.gemini.endpoint' => 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions',
            'recommendations.providers.gemini.key' => 'gemini-key',
            'recommendations.providers.gemini.model' => 'gemini-3.6-flash',
        ]);
        Settings::save(['llm_rationale' => 'enabled', 'llm_provider' => 'gemini']);

        Http::fake(['*' => Http::response([], 500)]);

        [$profile, $role, $overlap] = $this->buildContext('Data Analyst');
        $result = app(RecommendationRationalizer::class)->for($profile, $role, $overlap);

        $this->assertSame('template', $result['source']);
        $this->assertNotEmpty($result['text']);
    }

    public function test_groq_provider_hits_groq_endpoint_and_returns_llm_prose(): void
    {
        $endpoint = 'https://api.groq.com/openai/v1/chat/completions';

        config([
            'recommendations.providers.groq.endpoint' => $endpoint,
            'recommendations.providers.groq.key' => 'groq-key',
            'recommendations.providers.groq.model' => 'openai/gpt-oss-120b',
        ]);
        Settings::save(['llm_rationale' => 'enabled', 'llm_provider' => 'groq']);

        Http::fake([
            $endpoint => Http::response([
                'choices' => [
                    ['message' => ['content' => "The role\n\n<think>\nSome internal chain of thought.\n</think>\n\nfits because of your SQL background."]],
                ],
            ], 200),
        ]);

        [$profile, $role, $overlap] = $this->buildContext('Data Analyst');
        $result = app(RecommendationRationalizer::class)->for($profile, $role, $overlap);

        $this->assertSame('llm', $result['source']);
        $this->assertSame('The role fits because of your SQL background.', $result['text']);

        Http::assertSent(function ($request) use ($endpoint) {
            return $request->url() === $endpoint
                && $request->hasHeader('Authorization', 'Bearer groq-key')
                && $request['model'] === 'openai/gpt-oss-120b';
        });
    }

    public function test_groq_selected_but_only_gemini_configured_uses_template(): void
    {
        config([
            'recommendations.providers.gemini.endpoint' => 'https://generativelanguage.googleapis.com/v1/chat/completions',
            'recommendations.providers.gemini.key' => 'gemini-key',
            'recommendations.providers.groq.key' => '',
            'recommendations.providers.groq.endpoint' => '',
        ]);
        Settings::save(['llm_rationale' => 'enabled', 'llm_provider' => 'groq']);

        Http::fake();

        [$profile, $role, $overlap] = $this->buildContext('Data Analyst');
        $result = app(RecommendationRationalizer::class)->for($profile, $role, $overlap);

        $this->assertSame('template', $result['source']);
        Http::assertNothingSent();
    }

    public function test_gemini_selected_but_only_groq_configured_uses_template(): void
    {
        config([
            'recommendations.providers.gemini.key' => '',
            'recommendations.providers.gemini.endpoint' => '',
            'recommendations.providers.groq.endpoint' => 'https://api.groq.com/openai/v1/chat/completions',
            'recommendations.providers.groq.key' => 'groq-key',
        ]);
        Settings::save(['llm_rationale' => 'enabled', 'llm_provider' => 'gemini']);

        Http::fake();

        [$profile, $role, $overlap] = $this->buildContext('Data Analyst');
        $result = app(RecommendationRationalizer::class)->for($profile, $role, $overlap);

        $this->assertSame('template', $result['source']);
        Http::assertNothingSent();
    }
}
