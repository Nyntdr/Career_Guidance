<?php

namespace Tests\Feature\Ml;

use App\Services\Ml\ModelRepository;
use App\Services\ResumeParser;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleResumeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Settings::flush();
    }

    /**
     * @return list<string>
     */
    protected function typesFor(string $fixture, string $mode): array
    {
        Settings::save(['ml_enabled' => $mode]);

        $path = base_path('tests/Fixtures/resumes/'.$fixture);
        $this->assertFileExists($path);

        return array_column(app(ResumeParser::class)->segmentSections((string) file_get_contents($path)), 'type');
    }

    public function test_dict_only_path_on_pdf_fixture_is_stable(): void
    {
        $dict = $this->typesFor('ADVOCATE.txt', 'disabled');

        $this->assertSame('contact', $dict[0]);
        $this->assertContains('experience', $dict);
        $this->assertContains('skills', $dict);
        $this->assertContains('education', $dict);
    }

    public function test_ml_extends_dict_only_when_models_ready(): void
    {
        if (! app(ModelRepository::class)->isReady()) {
            $this->markTestSkipped('ML models are not trained in this environment.');
        }

        $dict = $this->typesFor('ADVOCATE.txt', 'disabled');
        $ml = $this->typesFor('ADVOCATE.txt', 'enabled');

        $this->assertSame('contact', $ml[0]);
        $this->assertContains('summary', $ml);

        // ML must not corrupt an otherwise clean dictionary segmentation into
        // the pathological section alternation seen on the real corpus.
        $this->assertLessThanOrEqual(count($dict) + 2, count($ml));
    }

    public function test_ml_rescues_unsegmented_docx_when_models_ready(): void
    {
        if (! app(ModelRepository::class)->isReady()) {
            $this->markTestSkipped('ML models are not trained in this environment.');
        }

        $dict = $this->typesFor('docx_balaji_gopalakrishnan_project_manager_txt.txt', 'disabled');

        $this->assertSame(['contact'], $dict);

        $ml = $this->typesFor('docx_balaji_gopalakrishnan_project_manager_txt.txt', 'enabled');

        $this->assertContains('skills', $ml);
    }
}
