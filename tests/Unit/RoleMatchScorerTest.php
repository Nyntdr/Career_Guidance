<?php

namespace Tests\Unit;

use App\Models\CareerRole;
use App\Models\CareerRoleSkill;
use App\Models\JobPosting;
use App\Models\JobRequiredSkill;
use App\Models\Skill;
use App\Models\User;
use App\Services\RoleMatchScorer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class RoleMatchScorerTest extends TestCase
{
    use RefreshDatabase;

    private function skill(string $name): Skill
    {
        return Skill::create(['name' => $name]);
    }

    /**
     * Build a user-skill map in the shape the callers pass: keyed by skill_id,
     * values exposing proficiency_level.
     */
    private function map(array $skillIdToLevel): Collection
    {
        return collect($skillIdToLevel)
            ->map(fn ($level, $skillId) => (object) [
                'skill_id' => $skillId,
                'proficiency_level' => $level,
            ]);
    }

    public function test_essential_requires_minimum_proficiency_but_preferred_only_requires_presence(): void
    {
        $essential = $this->skill('Kubernetes');
        $preferred = $this->skill('Helm');

        $role = CareerRole::create(['title' => 'SRE']);
        $this->requireRoleSkills($role, [
            $essential->id => ['importance' => 'essential', 'min_proficiency' => 3],
            $preferred->id => ['importance' => 'preferred', 'min_proficiency' => 3],
        ]);

        // Essential is below its minimum; preferred is present but also below
        // its minimum. Only the preferred skill may count.
        $result = app(RoleMatchScorer::class)->overlap($this->map([
            $essential->id => 1,
            $preferred->id => 1,
        ]), $role);

        $this->assertSame(30, $result['score'], 'Only the preferred tier may count when present below minimum.');
        $this->assertSame([$preferred->id], $result['matched']);
        $this->assertSame([$essential->id], $result['missing']);
    }

    public function test_weights_come_from_config_and_default_to_seventy_thirty(): void
    {
        $scorer = app(RoleMatchScorer::class);

        $this->assertSame(['essential' => 70, 'preferred' => 30], $scorer->weights());

        $essential = $this->skill('Go');
        $preferred = $this->skill('Docker');

        $role = CareerRole::create(['title' => 'Backend']);
        $this->requireRoleSkills($role, [
            $essential->id => ['importance' => 'essential', 'min_proficiency' => 1],
            $preferred->id => ['importance' => 'preferred', 'min_proficiency' => 1],
        ]);

        // Both tiers met => full score regardless of weighting.
        $this->assertSame(100, $scorer->overlap($this->map([
            $essential->id => 5,
            $preferred->id => 5,
        ]), $role)['score']);

        // Reweighting must move the score, proving config is the single source.
        config(['scoring.essential_weight' => 40, 'scoring.preferred_weight' => 60]);

        $this->assertSame(40, $scorer->overlap($this->map([
            $essential->id => 1,
        ]), $role)['score'], 'An essential-only match scores the essential weight.');
        $this->assertSame(60, $scorer->overlap($this->map([
            $preferred->id => 1,
        ]), $role)['score'], 'A preferred-only match scores the preferred weight.');
    }

    public function test_job_posting_requirements_resolve_skill_id_not_the_pivot_row_id(): void
    {
        $go = $this->skill('Go');
        $docker = $this->skill('Docker');

        $posting = JobPosting::create([
            'title' => 'Go Engineer',
            'company' => 'Acme',
            'raw_description' => 'Go and Docker',
        ]);

        // job_required_skills has its own uuid PK distinct from skill_id.
        JobRequiredSkill::create([
            'job_posting_id' => $posting->id,
            'skill_id' => $go->id,
            'importance' => 'required',
            'min_proficiency' => 2,
        ]);
        JobRequiredSkill::create([
            'job_posting_id' => $posting->id,
            'skill_id' => $docker->id,
            'importance' => 'preferred',
            'min_proficiency' => 2,
        ]);

        $requirements = $posting->requiredSkills()->get();
        $this->assertNotSame(
            $requirements->firstWhere('skill_id', $go->id)->id,
            $go->id,
            'Sanity: the pivot row PK must differ from skill_id for this test to be meaningful.'
        );

        $result = app(RoleMatchScorer::class)->score($this->map([
            $go->id => 3,
            $docker->id => 3,
        ]), $requirements);

        $this->assertSame(100, $result['score']);
        $this->assertEqualsCanonicalizing([$go->id, $docker->id], $result['matched']);
        $this->assertSame([], $result['missing']);
    }

    public function test_aggregate_shares_the_weighting_for_callers_with_their_own_match_rule(): void
    {
        $essential = $this->skill('Terraform');
        $preferred = $this->skill('Datadog');

        $role = CareerRole::create(['title' => 'SRE']);
        $this->requireRoleSkills($role, [
            $essential->id => ['importance' => 'essential', 'min_proficiency' => 4],
            $preferred->id => ['importance' => 'preferred', 'min_proficiency' => 4],
        ]);
        $requirements = $role->requiredSkills;

        // The ATS path treats a requirement as satisfied when the text mentions
        // it, ignoring proficiency. Weighting must be identical regardless.
        $byText = app(RoleMatchScorer::class)->aggregate($requirements, [
            $essential->id => true,
        ]);
        $this->assertSame(70, $byText['score']);
        $this->assertSame([$essential->id], $byText['matched']);
        // `missing` reports unmet ESSENTIAL requirements only; an unmet
        // preferred skill dilutes the score but is not reported as a gap.
        $this->assertSame([], $byText['missing']);

        config(['scoring.essential_weight' => 25, 'scoring.preferred_weight' => 75]);
        $reweighted = app(RoleMatchScorer::class)->aggregate($requirements, [
            $essential->id => true,
        ]);
        $this->assertSame(25, $reweighted['score']);
    }

    /**
     * career_role_skills has a uuid primary key with no default, so
     * attach() cannot supply one — insert the pivot rows explicitly, as the
     * CareerRoleSeeder does.
     *
     * @param  array<string, array{importance: string, min_proficiency: int}>  $pivots
     */
    private function requireRoleSkills(CareerRole $role, array $pivots): void
    {
        foreach ($pivots as $skillId => $pivot) {
            CareerRoleSkill::create([
                'career_role_id' => $role->id,
                'skill_id' => $skillId,
                'importance' => $pivot['importance'],
                'min_proficiency' => $pivot['min_proficiency'],
            ]);
        }

        $role->load('requiredSkills');
    }

    public function test_empty_requirements_score_zero_rather_than_erroring(): void
    {
        $scorer = app(RoleMatchScorer::class);

        $this->assertSame(
            ['score' => 0, 'matched' => [], 'missing' => []],
            $scorer->score(collect(), collect())
        );

        $role = CareerRole::create(['title' => 'Empty']);
        $this->assertSame(0, $scorer->overlap(collect(), $role)['score']);
    }
}
