<?php

namespace Tests\Feature;

use App\Models\CareerRole;
use App\Models\GapAssessment;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserSkill;
use Database\Seeders\CareerPathSeeder;
use Database\Seeders\CareerRoleSeeder;
use Database\Seeders\SkillAliasSeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditHighFixesTest extends TestCase
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

    public function test_flash_messages_are_shared_to_inertia(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([]);

        $response = $this->actingAs($user)
            ->withSession(['success' => 'Everything worked.'])
            ->get(route('dashboard'));

        $response->assertOk();

        $this->assertEquals('Everything worked.', $response->inertiaProps('flash')['success']);
    }

    public function test_onboarding_is_hidden_when_a_resume_exists(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([]);

        $profile->resumes()->create([
            'file_url' => 'resumes/demo.txt',
            'file_type' => 'text',
            'raw_text' => "Summary\nPython, SQL",
            'parsed_json' => ['sections' => [], 'skills' => []],
            'version' => 1,
            'is_primary' => true,
            'uploaded_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $this->assertFalse($response->inertiaProps('onboarding'));
    }

    public function test_assessment_requires_skills_or_target_role(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([]);

        $response = $this->actingAs($user)->post(route('assessment.run'));

        $response->assertRedirect(route('roadmap.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseCount('gap_assessments', 0);
        $this->assertDatabaseCount('gap_assessment_items', 0);
    }

    public function test_assessment_rerun_preserves_target_role(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([]);

        $dataAnalyst = CareerRole::where('title', 'Data Analyst')->firstOrFail();
        $otherRole = CareerRole::where('id', '!=', $dataAnalyst->id)->firstOrFail();

        foreach ($dataAnalyst->requiredSkills as $skill) {
            UserSkill::create([
                'profile_id' => $profile->id,
                'skill_id' => $skill->id,
                'proficiency_level' => 5,
            ]);
        }

        $this->actingAs($user)->post(route('assessment.run'), [
            'target_role_id' => $otherRole->id,
        ]);

        $this->actingAs($user)->post(route('assessment.run'));

        $latest = GapAssessment::where('profile_id', $profile->id)->latest()->first();

        $this->assertNotNull($latest);
        $this->assertEquals($otherRole->id, $latest->target_role_id);
        $this->assertNotNull($latest->skills_fingerprint);
    }

    public function test_roadmap_flags_stale_assessment_when_skills_change(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([]);

        $dataAnalyst = CareerRole::where('title', 'Data Analyst')->firstOrFail();

        foreach ($dataAnalyst->requiredSkills as $skill) {
            UserSkill::create([
                'profile_id' => $profile->id,
                'skill_id' => $skill->id,
                'proficiency_level' => 5,
            ]);
        }

        $this->actingAs($user)->post(route('assessment.run'), [
            'target_role_id' => $dataAnalyst->id,
        ]);

        $response = $this->actingAs($user)->get(route('roadmap.index'));
        $this->assertFalse($response->inertiaProps('isStale'));

        $firstUserSkill = UserSkill::where('profile_id', $profile->id)->firstOrFail();
        $firstUserSkill->update(['proficiency_level' => 2]);

        $stale = $this->actingAs($user)->get(route('roadmap.index'));
        $this->assertTrue($stale->inertiaProps('isStale'));
    }

    public function test_dashboard_hides_gap_insights_without_a_real_top_match(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([]);

        $irrelevant = Skill::create(['name' => 'Unrelated Skill XyZ']);

        UserSkill::create([
            'profile_id' => $profile->id,
            'skill_id' => $irrelevant->id,
            'proficiency_level' => 5,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $this->assertFalse($response->inertiaProps('gapInsights'));
        $this->assertSame([], $response->inertiaProps('skillsToDevelop'));
    }
}
