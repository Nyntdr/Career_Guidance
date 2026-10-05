<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkillCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\s\-]+$/', 'unique:skill_categories,name'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.regex' => 'The category name may only contain letters, numbers, spaces, and hyphens.',
        ];
    }
}
