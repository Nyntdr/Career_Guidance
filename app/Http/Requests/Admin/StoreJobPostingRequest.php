<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'seniority_level' => ['nullable', 'string', 'max:255'],
            'raw_description' => ['nullable', 'string'],
            'posted_at' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:255'],
            'skills' => ['nullable', 'array'],
            'skills.*.id' => ['required', 'exists:skills,id'],
            'skills.*.importance' => ['required', 'in:required,preferred'],
            'skills.*.min_proficiency' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
