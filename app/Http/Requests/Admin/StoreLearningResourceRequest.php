<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLearningResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\-\.\+,&()]+$/'],
            'provider' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\-\.\+,&()]+$/'],
            'url' => ['nullable', 'url', 'max:2048'],
            'resource_type' => ['required', 'string', 'in:course,article,video,book,certification,other'],
            'duration_hours' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'skill_ids' => ['nullable', 'array'],
            'skill_ids.*' => ['exists:skills,id'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'title.regex' => 'The title contains invalid characters.',
            'provider.regex' => 'The provider name contains invalid characters.',
        ];
    }
}
