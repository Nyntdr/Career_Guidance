<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\-\.\+,&()]+$/'],
            'company' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\-\.\+,&()]+$/'],
            'location' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\-\.,]+$/'],
            'seniority_level' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\s\-]+$/'],
            'raw_description' => ['nullable', 'string', 'max:5000'],
            'posted_at' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:255', 'url'],
            'skills' => ['nullable', 'array'],
            'skills.*.id' => ['required', 'exists:skills,id'],
            'skills.*.importance' => ['required', 'in:required,preferred'],
            'skills.*.min_proficiency' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'title.regex' => 'The title contains invalid characters.',
            'company.regex' => 'The company name contains invalid characters.',
            'location.regex' => 'The location contains invalid characters.',
            'seniority_level.regex' => 'The seniority level contains invalid characters.',
        ];
    }
}
