<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Resume;
use App\Models\ResumeSection;
use App\Models\Skill;
use App\Models\UserSkill;

class ResumeParser
{
    private const HEADINGS = [
        'professional experience' => 'experience',
        'work experience' => 'experience',
        'employment history' => 'experience',
        'experience' => 'experience',
        'technical skills' => 'skills',
        'core competencies' => 'skills',
        'proficiencies' => 'skills',
        'skills' => 'skills',
        'education' => 'education',
        'certifications' => 'certifications',
        'licenses' => 'certifications',
        'certificates' => 'certifications',
        'personal projects' => 'projects',
        'projects' => 'projects',
        'publications' => 'publications',
        'awards' => 'awards',
        'honors' => 'awards',
        'volunteer' => 'volunteer',
        'languages' => 'languages',
        'interests' => 'interests',
        'references' => 'references',
        'summary' => 'summary',
        'objective' => 'summary',
        'profile' => 'summary',
    ];

    public function parseAndStore(
        Profile $profile,
        string $rawText,
        ?string $fileName = null,
        ?string $fileUrl = null,
        ?string $fileType = null,
    ): Resume {
        $sections = $this->segmentSections($rawText);
        $skills = $this->extractSkills($rawText);

        $resume = Resume::create([
            'profile_id' => $profile->id,
            'file_url' => $fileUrl ?? '',
            'file_type' => $fileType ?? 'text',
            'raw_text' => $rawText,
            'parsed_json' => [
                'sections' => $sections,
                'skills' => $skills,
                'source_filename' => $fileName,
            ],
            'version' => $this->nextVersion($profile),
            'is_primary' => !Resume::where('profile_id', $profile->id)->exists(),
            'uploaded_at' => now(),
        ]);

        foreach ($sections as $index => $section) {
            ResumeSection::create([
                'resume_id' => $resume->id,
                'section_type' => $section['type'],
                'content' => $section['content'],
                'order_index' => $index,
            ]);
        }

        foreach ($skills as $skill) {
            UserSkill::firstOrCreate(
                ['profile_id' => $profile->id, 'skill_id' => $skill['id']],
                ['proficiency_level' => 3, 'source' => 'resume_extracted'],
            );
        }

        return $resume->load('sections');
    }

    private function nextVersion(Profile $profile): int
    {
        return (Resume::where('profile_id', $profile->id)->max('version') ?? 0) + 1;
    }

    public function segmentSections(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $sections = [];
        $current = 'contact';
        $buffer = [];

        $flush = function () use (&$sections, &$current, &$buffer) {
            $content = trim(implode("\n", $buffer));
            if ($content !== '') {
                $sections[] = ['type' => $current, 'content' => $content];
            }
            $buffer = [];
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            $normalized = strtolower(preg_replace('/^[\d.\-_)\s]+/', '', $trimmed));
            $type = $this->matchHeading($normalized);

            if ($type !== null) {
                $flush();
                $current = $type;
            } else {
                $buffer[] = $trimmed;
            }
        }

        $flush();

        return $sections;
    }

    private function matchHeading(string $normalizedLine): ?string
    {
        if (strlen($normalizedLine) > 60) {
            return null;
        }

        foreach (self::HEADINGS as $heading => $type) {
            if ($normalizedLine === $heading) {
                return $type;
            }
        }

        return null;
    }

    public function extractSkills(string $text): array
    {
        $lowerText = strtolower($text);
        $variants = [];

        Skill::with('aliases')->get()->each(function ($skill) use (&$variants) {
            $variants[$this->variantKey($skill->name)] = [
                'id' => $skill->id,
                'name' => $skill->name,
            ];

            foreach ($skill->aliases as $alias) {
                $variants[$this->variantKey($alias->alias)] = [
                    'id' => $skill->id,
                    'name' => $skill->name,
                ];
            }
        });

        $found = [];

        foreach ($variants as $key => $skill) {
            if (preg_match($this->boundaryPattern($key), $lowerText)) {
                $found[$skill['id']] = $skill;
            }
        }

        return array_values($found);
    }

    private function variantKey(string $value): string
    {
        return strtolower(trim($value));
    }

    private function boundaryPattern(string $needle): string
    {
        $quoted = preg_quote($needle, '/');

        return '/\b' . $quoted . '\b/i';
    }
}