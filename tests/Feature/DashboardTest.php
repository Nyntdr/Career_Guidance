<?php

namespace Tests\Feature;

use App\Models\CareerRole;
use App\Models\User;
use App\Models\UserSkill;
use Database\Seeders\CareerPathSeeder;
use Database\Seeders\CareerRoleSeeder;
use Database\Seeders\SkillAliasSeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            SkillSeeder::class,
            SkillAliasSeeder::class,
            CareerRoleSeeder::class,
            CareerPathSeeder::class,
        ]);
    }

    public function test_dashboard_renders_for_a_user_with_no_skills(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_dashboard_auto_generates_recommendations_for_a_user_with_skills(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([]);

        $role = CareerRole::where('title', 'Data Analyst')->firstOrFail();

        foreach ($role->requiredSkills as $skill) {
            UserSkill::create([
                'profile_id' => $profile->id,
                'skill_id' => $skill->id,
                'proficiency_level' => 5,
            ]);
        }

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();

        $this->assertNotEmpty($response->inertiaProps('careerMatches'));
        $this->assertNotNull($response->inertiaProps('topMatch'));
    }
}