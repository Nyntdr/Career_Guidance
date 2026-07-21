<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $skillId = $this->route('skill');

        return [
            'name' => ['required', 'string', 'max:255', "unique:skills,name,{$skillId}"],
            'category_id' => ['required', 'string', 'exists:skill_categories,id'],
        ];
    }
}
