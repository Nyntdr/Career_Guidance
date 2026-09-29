<?php

namespace Tests\Unit;

use App\Services\ResumeValidator;
use Tests\TestCase;

class ResumeValidatorTest extends TestCase
{
    private ResumeValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ResumeValidator();
    }

    public function test_short_text_fails_hard(): void
    {
        $result = $this->validator->assess('John Doe. Software Engineer.');

        $this->assertFalse($result['is_resume']);
        $this->assertSame('not_a_resume', $result['band']);
        $this->assertSame(0, $result['score']);
        $this->assertNotEmpty($result['reasons']);
    }

    public function test_prose_paragraph_without_sections_is_rejected(): void
    {
        $prose = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";

        $result = $this->validator->assess($prose);

        $this->assertFalse($result['is_resume']);
        $this->assertSame('not_a_resume', $result['band']);
        $this->assertLessThan(3, $result['score']);
    }

    public function test_strong_resume_clears_strong_band(): void
    {
        $resumeText = "Jordan Lee\n"
            ."jordan.lee@example.com | +1 555-0199 | Springfield\n\n"
            ."Professional Summary\n"
            ."Data analyst with 6 years of experience turning messy data into decisions.\n\n"
            ."Work Experience\n"
            ."Senior Data Analyst, Acme Corp (2019 - Present)\n"
            ."• Built dashboards and SQL pipelines used across the company.\n"
            ."• Managed team of 4 junior analysts.\n\n"
            ."Education\n"
            ."University of Springfield, BSc Data Science\n\n"
            ."Technical Skills\n"
            ."Python, SQL, Tableau";

        $result = $this->validator->assess($resumeText);

        $this->assertTrue($result['is_resume']);
        $this->assertSame('strong', $result['band']);
        $this->assertGreaterThanOrEqual(6, $result['score']);
    }

    public function test_borderline_resume_clears_borderline_band(): void
    {
        $resumeText = "Sabina Gurung\n"
            ."sabina.gurung@example.com | Pokhara, Nepal | +977 9800000004\n\n"
            ."MY DETAILS\n"
            ."I have worked in retail and food service for the last few years and I enjoy helping people and being part of a team.\n\n"
            ."WHERE I WORKED\n"
            ."Supermarket in Pokhara. Mostly I take care of customers when they come in and answer their questions.\n\n"
            ."ABOUT MY STUDIES\n"
            ."I finished high school and then I studied a bit more at a college certificate program.";

        $result = $this->validator->assess($resumeText);

        $this->assertTrue($result['is_resume']);
        $this->assertContains($result['band'], ['strong', 'borderline']);
        $this->assertGreaterThanOrEqual(3, $result['score']);
    }
}
