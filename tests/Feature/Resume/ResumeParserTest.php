<?php

namespace Tests\Feature\Resume;

use App\Models\Profile;
use App\Models\Skill;
use App\Models\SkillAlias;
use App\Models\User;
use App\Services\ResumeParser;
use App\Services\ResumeTextExtractor;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ResumeParserTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Settings::flush();

        // Keep parsing deterministic by disabling the (opt-in, model-gated) ML path.
        Settings::save(['ml_enabled' => 'disabled']);
    }

    public function test_parse_and_store_segments_sections_and_extracts_skills(): void
    {
        $user = User::factory()->create();
        $profile = Profile::create(['user_id' => $user->id]);

        $python = Skill::create(['name' => 'Python']);
        SkillAlias::create(['skill_id' => $python->id, 'alias' => 'py']);
        Skill::create(['name' => 'SQL']);

        $text = "John Doe\n123 Main St\n\nSummary\nData specialist with 6 years of experience.\n\nWork Experience\nLed data pipelines on a large platform.\n\nTechnical Skills\nPython, SQL, Docker\n\nEducation\nState University, BSc\n";

        $resume = app(ResumeParser::class)->parseAndStore($profile, $text, 'resume.txt');

        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'is_primary' => 1, 'version' => 1]);

        $this->assertSame(
            ['contact', 'summary', 'experience', 'skills', 'education'],
            $resume->sections->sortBy('order_index')->pluck('section_type')->values()->all(),
        );

        $skills = $profile->userSkills()->with('skill')->get();
        $this->assertTrue($skills->pluck('skill.name')->contains('Python'));
        $this->assertTrue($skills->pluck('skill.name')->contains('SQL'));

        $this->assertSame('resume_extracted', $skills->first()->source);
    }

    public function test_parse_section_extraction_is_stable_for_primary_resume_versions(): void
    {
        $user = User::factory()->create();
        $profile = Profile::create(['user_id' => $user->id]);
        Skill::create(['name' => 'Python']);

        $text = "John Doe\nSkills\nPython";

        $first = app(ResumeParser::class)->parseAndStore($profile, $text);
        $second = app(ResumeParser::class)->parseAndStore($profile, $text);

        $this->assertTrue($first->is_primary);
        $this->assertFalse($second->is_primary);
        $this->assertSame(1, $first->version);
        $this->assertSame(2, $second->version);
        $this->assertSame(['contact', 'skills'], $second->sections->sortBy('order_index')->pluck('section_type')->values()->all());
    }

    public function test_clean_strips_non_latin_scripts(): void
    {
        $extractor = app(ResumeTextExtractor::class);

        $cleaned = $this->invokeClean($extractor, "Skills: Python, SQL\n\nНавыки: Питон\n\nSkills 추가: 자바스크립트\nРезюме\n");

        $this->assertStringNotContainsString('Навыки', $cleaned);
        $this->assertStringNotContainsString('Питон', $cleaned);
        $this->assertStringNotContainsString('자바스크립트', $cleaned);
        $this->assertStringNotContainsString('Резюме', $cleaned);
        $this->assertStringContainsString('Skills', $cleaned);
        $this->assertStringContainsString('Python', $cleaned);
        $this->assertStringContainsString('SQL', $cleaned);
    }

    private function invokeClean(ResumeTextExtractor $extractor, string $text): string
    {
        $method = new \ReflectionMethod($extractor, 'clean');

        return $method->invoke($extractor, $text);
    }
}
