<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\s\-\.\+#]+$/', 'unique:skills,name'],
            'category_id' => ['required', 'string', 'exists:skill_categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'The skill name contains invalid characters.',
        ];
    }
}
