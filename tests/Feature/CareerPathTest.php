<?php

namespace Tests\Feature;

use App\Models\CareerPath;
use App\Models\CareerRole;
use App\Models\User;
use Database\Seeders\CareerPathSeeder;
use Database\Seeders\CareerRoleSeeder;
use Database\Seeders\SkillAliasSeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerPathTest extends TestCase
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

    public function test_seeder_creates_expected_edges_between_seeded_roles(): void
    {
        $this->assertGreaterThanOrEqual(10, CareerPath::count());

        $dataAnalyst = CareerRole::where('title', 'Data Analyst')->firstOrFail();
        $dataScientist = CareerRole::where('title', 'Data Scientist')->firstOrFail();
        $mlEngineer = CareerRole::where('title', 'Machine Learning Engineer')->firstOrFail();

        $this->assertDatabaseHas('career_paths', [
            'from_role_id' => $dataAnalyst->id,
            'to_role_id' => $dataScientist->id,
            'typical_years' => 2,
        ]);

        $this->assertDatabaseHas('career_paths', [
            'from_role_id' => $dataScientist->id,
            'to_role_id' => $mlEngineer->id,
        ]);
    }

    public function test_seeder_is_idempotent_for_repeated_runs(): void
    {
        (new CareerPathSeeder)->run();

        $dataAnalyst = CareerRole::where('title', 'Data Analyst')->firstOrFail();
        $dataScientist = CareerRole::where('title', 'Data Scientist')->firstOrFail();

        $this->assertSame(
            1,
            CareerPath::where('from_role_id', $dataAnalyst->id)
                ->where('to_role_id', $dataScientist->id)
                ->count(),
        );
    }

    public function test_careers_detail_page_exposes_outgoing_and_incoming_paths(): void
    {
        $user = User::factory()->create();
        $softwareEngineer = CareerRole::where('title', 'Software Engineer')->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('careers.show', $softwareEngineer->id));

        $response->assertOk();

        $outgoingPaths = $response->inertiaProps('outgoingPaths');
        $incomingPaths = $response->inertiaProps('incomingPaths');

        $this->assertNotEmpty($outgoingPaths);
        $this->assertNotEmpty($incomingPaths);

        $outgoingTitles = collect($outgoingPaths)->pluck('to_role')->all();
        $this->assertContains('DevOps Engineer', $outgoingTitles);
        $this->assertContains('Machine Learning Engineer', $outgoingTitles);

        $incomingTitles = collect($incomingPaths)->pluck('from_role')->all();
        $this->assertContains('Frontend Developer', $incomingTitles);
        $this->assertContains('Backend Developer', $incomingTitles);
    }
}