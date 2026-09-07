<?php

namespace Tests\Feature\Ml;

use App\Services\Ml\ModelRepository;
use App\Services\Ml\ResumeLineVectorizer;
use App\Services\Ml\ResumeSegmenter;
use App\Services\Ml\SectionClassifier;
use App\Services\Ml\SectionClusterer;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ResumeSegmenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Settings holds a static cache that spans test methods.
        Settings::flush();
    }

    protected const HEADINGS = [
        'professional experience' => 'experience',
        'work experience' => 'experience',
        'experience' => 'experience',
        'technical skills' => 'skills',
        'skills' => 'skills',
        'education' => 'education',
        'summary' => 'summary',
    ];

    public function test_without_ml_it_behaves_like_the_plain_dictionary(): void
    {
        Settings::save(['ml_enabled' => 'disabled']);

        $segmenter = new ResumeSegmenter(
            Mockery::mock(ModelRepository::class),
            Mockery::mock(SectionClassifier::class),
            Mockery::mock(SectionClusterer::class),
        );

        $text = "John Doe\n123 Main St\nWork Experience\nLed a team of five engineers to ship a recommendation engine.\nEducation\nUniversity of Springfield, BSc CS\nSkills\nPython, SQL";

        $sections = $segmenter->segment($text, self::HEADINGS);

        $this->assertSame(['contact', 'experience', 'education', 'skills'], array_column($sections, 'type'));
        $this->assertSame("John Doe\n123 Main St", $sections[0]['content']);
        $this->assertSame('Led a team of five engineers to ship a recommendation engine.', $sections[1]['content']);
    }

    public function test_a_fuzzy_heading_is_detected_by_the_ml_path(): void
    {
        $repository = Mockery::mock(ModelRepository::class);
        $repository->allows('isReady')->andReturn(true);
        $repository->allows('vocab')->andReturn(ResumeLineVectorizer::BASE_VOCAB);

        // "John Doe" -> contact (won't switch), "Technical Proficiencies" -> skills.
        $classifier = Mockery::mock(SectionClassifier::class);
        $classifier->allows('predictMany')
            ->andReturn([
                ['label' => 'contact', 'score' => 0.8],
                ['label' => 'skills', 'score' => 0.92],
                ['label' => 'skills', 'score' => 0.6],
            ]);

        $clusterer = Mockery::mock(SectionClusterer::class);
        $clusterer->allows('clusterMany')->andReturn([0, 1, 1]);

        $segmenter = new ResumeSegmenter($repository, $classifier, $clusterer);

        $sections = $segmenter->segment("\nJohn Doe\n\nTechnical Proficiencies\nPython, SQL", self::HEADINGS);

        $this->assertSame(['contact', 'skills'], array_column($sections, 'type'));
        $this->assertSame('ml', $sections[1]['method']);
        $this->assertSame('Python, SQL', $sections[1]['content']);
    }

    public function test_a_long_content_line_never_starts_a_section(): void
    {
        $repository = Mockery::mock(ModelRepository::class);
        $repository->allows('isReady')->andReturn(true);
        $repository->allows('vocab')->andReturn(ResumeLineVectorizer::BASE_VOCAB);

        // Even a confident mislabel of a long content line must not switch.
        $classifier = Mockery::mock(SectionClassifier::class);
        $classifier->allows('predictMany')->andReturn([
            ['label' => 'contact', 'score' => 0.8],
            ['label' => 'skills', 'score' => 0.9],
            ['label' => 'skills', 'score' => 0.85],
            ['label' => 'skills', 'score' => 0.85],
        ]);

        $clusterer = Mockery::mock(SectionClusterer::class);
        $clusterer->allows('clusterMany')->andReturn([0, 1, 1, 1]);

        $segmenter = new ResumeSegmenter($repository, $classifier, $clusterer);

        $text = "\nJohn Doe\n\nTechnical Proficiencies\nPython, SQL\nLed a team of five engineers to ship a recommendation engine for millions of users";
        $sections = $segmenter->segment($text, self::HEADINGS);

        $this->assertSame(['contact', 'skills'], array_column($sections, 'type'));
        $this->assertSame('John Doe', $sections[0]['content']);
        $this->assertStringContainsString('Python, SQL', $sections[1]['content']);
        $this->assertStringContainsString('Led a team of five engineers', $sections[1]['content']);
    }

    public function test_low_confidence_prediction_is_ignored(): void
    {
        $repository = Mockery::mock(ModelRepository::class);
        $repository->allows('isReady')->andReturn(true);
        $repository->allows('vocab')->andReturn(ResumeLineVectorizer::BASE_VOCAB);

        $classifier = Mockery::mock(SectionClassifier::class);
        $classifier->allows('predictMany')->andReturn([
            ['label' => 'skills', 'score' => 0.1],
        ]);

        $clusterer = Mockery::mock(SectionClusterer::class);
        $clusterer->allows('clusterMany')->andReturn([0]);

        $segmenter = new ResumeSegmenter($repository, $classifier, $clusterer);

        $sections = $segmenter->segment('Areas of Expertise', self::HEADINGS);

        $this->assertSame(['contact'], array_column($sections, 'type'));
    }
}
