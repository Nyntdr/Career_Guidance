<?php

namespace Tests\Feature\Ml;

use App\Services\Ml\ModelRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MlTrainCommandTest extends TestCase
{
    use RefreshDatabase;

    protected string $modelDir;

    protected string $trainingDir;

    protected string $vocabPath;

    protected function setUp(): void
    {
        parent::setUp();

        $base = sys_get_temp_dir().'/career_ml_test_'.uniqid();
        $this->modelDir = $base.'/models';
        $this->trainingDir = $base.'/training';
        $this->vocabPath = $base.'/vocab.json';

        config([
            'ml.model_path' => $this->modelDir,
            'ml.training_path' => $this->trainingDir,
            'ml.vocab_path' => $this->vocabPath,
            'ml.estimators' => 20,
            'ml.ratio' => 0.5,
        ]);

        $fixture = database_path('ml/section_lines.json');
        mkdir($this->trainingDir, 0775, true);
        copy($fixture, $this->trainingDir.'/section_lines.json');
    }

    protected function tearDown(): void
    {
        foreach ([$this->vocabPath, $this->modelDir, $this->trainingDir, dirname($this->vocabPath)] as $path) {
            if (is_dir($path)) {
                foreach (glob($path.'/*') ?: [] as $file) {
                    @unlink($file);
                }
                @rmdir($path);
            } elseif (is_file($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_train_command_persists_ready_models(): void
    {
        $this->artisan('ml:train')
            ->expectsOutputToContain('Saved resume_section_classifier.model')
            ->expectsOutputToContain('Saved resume_section_clusterer.model')
            ->assertExitCode(0);

        $this->assertFileExists($this->modelDir.'/resume_section_classifier.model');
        $this->assertFileExists($this->modelDir.'/resume_section_clusterer.model');
        $this->assertFileExists($this->vocabPath);

        $repository = new ModelRepository;
        $this->assertTrue($repository->isReady());
        $this->assertNotEmpty($repository->vocab());
    }

    public function test_train_command_uses_shipped_fixture_as_fallback(): void
    {
        @unlink($this->trainingDir.'/section_lines.json');

        $this->artisan('ml:train')
            ->expectsOutputToContain('Saved resume_section_classifier.model')
            ->assertExitCode(0);

        $this->assertFileExists($this->trainingDir.'/section_lines.json');
    }
}
