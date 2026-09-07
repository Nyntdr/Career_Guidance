<?php

namespace Tests\Feature\Resume;

use App\Models\Profile;
use App\Models\Skill;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ResumeUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Settings::flush();

        // Deterministic parsing: ML path is model-gated and off by default in tests.
        Settings::save(['ml_enabled' => 'disabled']);
    }

    private function userWithProfile(): User
    {
        $user = User::factory()->create();
        Profile::create(['user_id' => $user->id]);

        return $user;
    }

    private function fixtureFile(string $name, string $clientName, string $mime): UploadedFile
    {
        return new UploadedFile(
            base_path("tests/Fixtures/files/{$name}"),
            $clientName,
            $mime,
            null,
            true,
        );
    }

    public function test_docx_upload_extracts_text_segments_sections_and_skills(): void
    {
        Skill::create(['name' => 'Python']);
        Skill::create(['name' => 'SQL']);

        $response = $this->actingAs($this->userWithProfile())
            ->post(route('resume.store'), [
                'resume_file' => $this->fixtureFile(
                    'upload-sample.docx',
                    'resume.docx',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ),
            ]);

        $response->assertSessionHasNoErrors();

        $resume = \App\Models\Resume::query()->firstOrFail();

        $this->assertSame('docx', $resume->file_type);
        $this->assertNotEmpty($resume->raw_text);
        $this->assertStringContainsString('Senior Data Analyst, Acme Corp', $resume->raw_text);
        // "Professional Summary" is a fuzzy heading (not in the dictionary), so with the
        // ML path disabled it stays in the contact block — exactly what the ML path exists to catch.
        $this->assertSame(
            ['contact', 'experience', 'education', 'skills'],
            $resume->sections->sortBy('order_index')->pluck('section_type')->values()->all(),
        );
        $this->assertStringContainsString('Professional Summary', $resume->sections->sortBy('order_index')->first()->content);

        $skillNames = $resume->profile->userSkills()->with('skill')->get()->pluck('skill.name')->all();
        $this->assertContains('Python', $skillNames);
        $this->assertContains('SQL', $skillNames);
    }

    public function test_pdf_upload_extracts_text_and_segments_sections(): void
    {
        $response = $this->actingAs($this->userWithProfile())
            ->post(route('resume.store'), [
                'resume_file' => $this->fixtureFile('upload-sample.pdf', 'resume.pdf', 'application/pdf'),
            ]);

        $response->assertSessionHasNoErrors();

        $resume = \App\Models\Resume::query()->firstOrFail();

        $this->assertSame('pdf', $resume->file_type);
        $this->assertStringContainsString('Work Experience', $resume->raw_text);
        $this->assertSame(
            ['contact', 'experience', 'education', 'skills'],
            $resume->sections->sortBy('order_index')->pluck('section_type')->values()->all(),
        );
    }

    public function test_txt_upload_still_works(): void
    {
        $response = $this->actingAs($this->userWithProfile())
            ->post(route('resume.store'), [
                'resume_file' => $this->fixtureFile('upload-sample.txt', 'resume.txt', 'text/plain'),
            ]);

        $response->assertSessionHasNoErrors();

        $resume = \App\Models\Resume::query()->firstOrFail();

        $this->assertSame('txt', $resume->file_type);
        $this->assertStringContainsString('Jordan Lee', $resume->raw_text);
    }

    public function test_unsupported_file_type_is_rejected(): void
    {
        $response = $this->actingAs($this->userWithProfile())
            ->post(route('resume.store'), [
                'resume_file' => UploadedFile::fake()->create('resume.exe', 10),
            ]);

        $response->assertSessionHasErrors('resume_file');
    }
}