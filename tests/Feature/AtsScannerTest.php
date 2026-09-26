<?php

namespace Tests\Feature;

use App\Models\AtsMatchedKeyword;
use App\Models\JobPosting;
use App\Models\JobRequiredSkill;
use App\Models\Profile;
use App\Models\Resume;
use App\Models\ResumeSection;
use App\Models\Skill;
use App\Models\SkillAlias;
use App\Models\User;
use App\Services\AtsScanner;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtsScannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Settings::flush();
        Settings::save(['ml_enabled' => 'disabled']);
    }

    private function resumeFor(string $text): Resume
    {
        $user = User::factory()->create();
        $profile = Profile::create(['user_id' => $user->id]);

        $resume = Resume::create([
            'profile_id' => $profile->id,
            'file_url' => '',
            'file_type' => 'text',
            'raw_text' => $text,
            'parsed_json' => [],
            'version' => 1,
            'is_primary' => true,
        ]);

        foreach (['summary', 'experience', 'education', 'skills'] as $index => $type) {
            ResumeSection::create([
                'resume_id' => $resume->id,
                'section_type' => $type,
                'content' => "Section {$type} body text.",
                'order_index' => $index,
            ]);
        }

        return $resume->fresh();
    }

    public function test_job_description_using_an_alias_is_counted_as_a_keyword_hit(): void
    {
        // Canonical skill name is "Kubernetes"; the JD only says "K8s".
        $skill = Skill::create(['name' => 'Kubernetes']);
        SkillAlias::create(['skill_id' => $skill->id, 'alias' => 'K8s']);

        $job = JobPosting::create([
            'title' => 'Platform Engineer',
            'company' => 'Acme',
            'raw_description' => 'You will run K8s in production and manage K8s clusters.',
        ]);

        JobRequiredSkill::create([
            'job_posting_id' => $job->id,
            'skill_id' => $skill->id,
            'importance' => 'required',
            'min_proficiency' => 3,
        ]);

        $resume = $this->resumeFor("PROFESSIONAL SUMMARY\nExperienced platform engineer.\n- Ran K8s clusters daily.\n");

        app(AtsScanner::class)->scan($resume, $job->fresh());

        $keyword = AtsMatchedKeyword::where('keyword', 'Kubernetes')->firstOrFail();

        $this->assertTrue((bool) $keyword->matched, 'The resume mentions the alias, so it is matched.');
        $this->assertSame(
            2,
            (int) $keyword->frequency_in_jd,
            'The JD mentions the alias twice, so frequency_in_jd must be alias-aware too.',
        );
    }

    public function test_alias_free_keyword_frequency_is_unchanged(): void
    {
        $skill = Skill::create(['name' => 'Docker']);
        SkillAlias::create(['skill_id' => $skill->id, 'alias' => 'Containerisation']);

        $job = JobPosting::create([
            'title' => 'DevOps Engineer',
            'company' => 'Acme',
            'raw_description' => 'Docker and Docker Compose required.',
        ]);

        JobRequiredSkill::create([
            'job_posting_id' => $job->id,
            'skill_id' => $skill->id,
            'importance' => 'required',
            'min_proficiency' => 3,
        ]);

        $resume = $this->resumeFor("SUMMARY\nPlatform engineer.\n- Shipped Docker images weekly.\n");

        app(AtsScanner::class)->scan($resume, $job->fresh());

        $keyword = AtsMatchedKeyword::where('keyword', 'Docker')->firstOrFail();

        $this->assertTrue((bool) $keyword->matched);
        $this->assertSame(2, (int) $keyword->frequency_in_jd);
        $this->assertSame(1, (int) $keyword->frequency_in_resume);
    }
}
