<?php

namespace App\Services;

use App\Models\CareerRole;
use App\Models\Profile;
use App\Support\Settings;
use Illuminate\Support\Facades\Http;

class RecommendationRationalizer
{
    /**
     * Produce a short human-readable explanation for a recommendation.
     * Prefers the LLM provider when it is enabled and configured, otherwise
     * falls back to a deterministic template (also used on any LLM failure).
     *
     * @param  array{score:int, matched:array<int,string>, missing:array<int,string>}  $overlap
     * @return array{text:string, source:string}
     */
    public function for(Profile $profile, CareerRole $role, array $overlap): array
    {
        if ($this->isEnabled()) {
            try {
                $text = $this->llm($profile, $role, $overlap);

                if ($text !== null) {
                    return ['text' => $text, 'source' => 'llm'];
                }
            } catch (\Throwable $e) {
                // Swallow provider failures and fall back to the template.
            }
        }

        return ['text' => $this->template($profile, $role, $overlap), 'source' => 'template'];
    }

    /**
     * Whether an LLM provider is configured AND switched on via the Admin
     * "AI rationale (LLM)" setting.
     */
    public function isEnabled(): bool
    {
        if ((string) Settings::get('llm_rationale', config('recommendations.rationale_default', 'disabled')) !== 'enabled') {
            return false;
        }

        $provider = $this->providerConfig();

        return $provider !== null
            && $provider['endpoint'] !== ''
            && $provider['key'] !== '';
    }

    /**
     * Resolve the active provider's connection details from the Admin setting
     * (Settings -> ML -> "AI rationale provider"). Falls back to the config
     * default when nothing is stored yet.
     *
     * @return array{endpoint:string, key:string, model:string}|null
     */
    protected function providerConfig(): ?array
    {
        $name = (string) Settings::get('llm_provider', config('recommendations.default_provider', 'gemini'));
        $provider = config("recommendations.providers.{$name}");

        return is_array($provider) && isset($provider['endpoint'], $provider['key'], $provider['model'])
            ? $provider
            : null;
    }

    /**
     * @param  array{score:int, matched:array<int,string>, missing:array<int,string>}  $overlap
     */
    protected function llm(Profile $profile, CareerRole $role, array $overlap): ?string
    {
        $provider = $this->providerConfig();

        $payload = [
            'model' => (string) $provider['model'],
            'messages' => $this->messages($profile, $role, $overlap),
            'temperature' => 0.4,
            'max_tokens' => (int) config('recommendations.max_tokens', 1024),
        ];

        $attempts = 0;
        $retryable = false;

        do {
            $response = Http::timeout((int) config('recommendations.timeout', 60))
                ->withToken((string) $provider['key'])
                ->acceptJson()
                ->post((string) $provider['endpoint'], $payload);

            $attempts++;
            $retryable = in_array($response->status(), [429, 503], true) && $attempts < 3;

            if ($retryable) {
                usleep($attempts * 400_000);
            }
        } while ($retryable);

        if (! $response->successful()) {
            return null;
        }

        $text = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($text) || trim($text) === '') {
            return null;
        }

        // Reasoning models (e.g. Llama 4 or Qwen 3 behind some endpoints) may
        // wrap their chain of thought in a  thinking ... </think> block. Strip
        // it so only the visible rationale is stored.
        $text = (string) preg_replace('/\s*<think(?:ing)?>.*?<\/think>\s*/s', ' ', $text);

        if (str_contains($text, '<think')) {
            $text = (string) preg_replace('/\s*<think.*$/s', ' ', $text);
        }

        $text = trim((string) preg_replace('/\s+/', ' ', $text));

        return $text === '' ? null : $text;
    }

    /**
     * Build the system + user messages for the active prompt version.
     * Admins pick between the concise and detailed variants in
     * Settings -> ML -> "AI rationale prompt".
     *
     * @param  array{score:int, matched:array<int,string>, missing:array<int,string>}  $overlap
     * @return array<int, array{role:string, content:string}>
     */
    protected function messages(Profile $profile, CareerRole $role, array $overlap): array
    {
        $detailed = (string) Settings::get('llm_prompt_version', 'concise') === 'detailed';

        if ($detailed) {
            return [
                [
                    'role' => 'system',
                    'content' => 'You are a career counselor and labor-market analyst. Write clear, plain prose in 4-6 sentences — no lists, headings, bullets, or markdown. Be specific and personalized to the candidate\'s actual profile. Ground every claim only in the facts provided; never invent skills, salary figures, or industries not given.',
                ],
                [
                    'role' => 'user',
                    'content' => $this->promptDetailed($profile, $role, $overlap),
                ],
            ];
        }

        return [
            [
                'role' => 'system',
                'content' => 'You are a career counselor. Reply in plain prose with no lists, headings, or markdown.',
            ],
            [
                'role' => 'user',
                'content' => $this->prompt($profile, $role, $overlap),
            ],
        ];
    }

    /**
     * @param  array{score:int, matched:array<int,string>, missing:array<int,string>}  $overlap
     */
    protected function prompt(Profile $profile, CareerRole $role, array $overlap): string
    {
        $nameById = $role->requiredSkills->keyBy('id');
        $matched = collect($overlap['matched'])->map(fn ($id) => (string) $nameById->get($id)?->name);
        $missing = collect($overlap['missing'])->map(fn ($id) => (string) $nameById->get($id)?->name);

        return implode("\n", [
            'Role: '.$role->title,
            'Role description: '.($role->description ?: 'Not provided'),
            'Skills the candidate already has: '.($this->listify($matched) ?: 'None from this role'),
            'Skills the candidate still needs: '.($this->listify($missing) ?: 'None'),
            'Candidate headline: '.($profile->headline ?: 'Not provided'),
            'Candidate target roles: '.($profile->target_roles ? implode(', ', $profile->target_roles) : 'Not specified'),
            '',
            'In 2-3 sentences, tell the candidate why this role is a realistic fit and the single most useful next step.',
        ]);
    }

    /**
     * Detailed prompt: richer candidate + role context (salary, seniority,
     * market outlook, required vs preferred skills, education, experience).
     *
     * @param  array{score:int, matched:array<int,string>, missing:array<int,string>}  $overlap
     */
    protected function promptDetailed(Profile $profile, CareerRole $role, array $overlap): string
    {
        $byId = $role->requiredSkills->keyBy('id');

        $matched = collect($overlap['matched'])
            ->mapWithKeys(fn ($id) => [$id => $byId->get($id)])
            ->filter();

        $missing = collect($overlap['missing'])
            ->mapWithKeys(fn ($id) => [$id => $byId->get($id)])
            ->filter();

        $roleRequired = $this->listify(
            $byId->filter(fn ($skill) => $skill->pivot?->importance === 'required')->pluck('name')
        ) ?: 'None listed';

        $rolePreferred = $this->listify(
            $byId->filter(fn ($skill) => $skill->pivot?->importance !== 'required')->pluck('name')
        ) ?: 'None listed';

        $salary = $role->avg_salary_range;
        $salaryText = is_array($salary) && isset($salary['min'], $salary['max'])
            ? 'NPR '.number_format((int) $salary['min']).' – '.number_format((int) $salary['max']).' per year'
            : null;

        $education = $profile->education()->orderByDesc('end_date')->first();
        $educationText = null;

        if ($education) {
            $pieces = array_filter([$education->field_of_study, $education->degree, $education->institution]);
            $educationText = $pieces === [] ? null : implode(', ', $pieces);
        }

        return implode("\n", [
            '--- The role ---',
            'Role to evaluate: '.$role->title,
            'What this role is: '.($role->description ?: 'Not provided'),
            'Typical seniority level: '.($role->typical_seniority ?: 'Not stated'),
            'Salary range (NPR/year): '.($salaryText ?? 'Not stated'),
            'Market outlook: '.($role->growth_outlook ?: 'Not stated'),
            'Required skills: '.$roleRequired,
            'Preferred (nice-to-have) skills: '.$rolePreferred,
            '',
            '--- Candidate profile ---',
            'Headline: '.($profile->headline ?: 'Not provided'),
            'Current title: '.($profile->current_title ?: 'Not provided'),
            'Years of experience: '.($profile->years_experience ?? 'Not stated'),
            'Location: '.($profile->location ?: 'Not stated'),
            'Target roles: '.($profile->target_roles ? implode(', ', $profile->target_roles) : 'Not specified'),
            'Target industries: '.($profile->target_industries ? implode(', ', $profile->target_industries) : 'Not specified'),
            'Education background: '.($educationText ?? 'Not provided'),
            'Career goals/interests: '.($profile->bio ?: 'Not provided'),
            'Skills the candidate already has (of those this role needs): '.($this->listify($matched->pluck('name')) ?: 'None'),
            'Skills the candidate is still missing for this role: '.($this->listify($missing->pluck('name')) ?: 'None'),
            '',
            '--- Task ---',
            'Write a concise career recommendation (4-6 plain-prose sentences) covering:',
            "1. Why this role is a realistic fit given the candidate's skills, background, and goals.",
            '2. What the candidate already brings to the table — lead with their strongest matches.',
            '3. The most important gaps and a realistic way to close them (name concrete paths: project types, certifications, or portfolio work).',
            '4. The single most useful next step, specific enough to act on this week.',
            'Use only the facts above; if something is missing, skip that angle — do not invent it.',
        ]);
    }

    /**
     * @param  array{score:int, matched:array<int,string>, missing:array<int,string>}  $overlap
     */
    public function template(Profile $profile, CareerRole $role, array $overlap): string
    {
        $nameById = $role->requiredSkills->keyBy('id');
        $matched = collect($overlap['matched'])->map(fn ($id) => (string) $nameById->get($id)?->name)->filter();
        $missing = collect($overlap['missing'])->map(fn ($id) => (string) $nameById->get($id)?->name)->filter();

        $parts = [];

        if ($matched->isNotEmpty()) {
            $parts[] = 'Your existing skills in '.$this->listify($matched).' align well with this role.';
        } elseif ($missing->isNotEmpty()) {
            $parts[] = 'This role centres on '.$this->listify($missing).', which are not yet on your profile.';
        }

        if ($missing->isNotEmpty()) {
            $parts[] = 'Closing the '.$this->listify($missing).' gap would move you toward a strong match.';
        }

        if ($role->growth_outlook) {
            $parts[] = 'Industry outlook: '.trim($role->growth_outlook, ". \t\n\r\0\x0B").'.';
        }

        $education = $profile->education()->orderByDesc('end_date')->first();

        if ($education && $education->field_of_study) {
            $parts[] = 'Your background in '.$education->field_of_study.' supports this direction.';
        }

        if ($parts === []) {
            $parts[] = 'Start by adding relevant skills to your profile to unlock a personalized match for this role.';
        }

        return implode(' ', $parts);
    }

    protected function listify($items): string
    {
        $items = collect($items)->filter()->values()->map(fn ($item) => trim((string) $item))->filter();

        if ($items->isEmpty()) {
            return '';
        }

        if ($items->count() === 1) {
            return (string) $items->first();
        }

        $last = $items->pop();

        return $items->implode(', ').' and '.$last;
    }
}
