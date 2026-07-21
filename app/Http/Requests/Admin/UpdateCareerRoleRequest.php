<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCareerRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('career_role');

        return [
            'title' => ['required', 'string', 'max:255', "unique:career_roles,title,{$roleId}"],
            'description' => ['required', 'string'],
            'typical_seniority' => ['required', 'string', 'max:255'],
            'avg_salary_range' => ['required', 'json'],
            'growth_outlook' => ['required', 'string', 'max:255'],
            'skills' => ['sometimes', 'array', 'min:1'],
            'skills.*.id' => ['required_with:skills', 'exists:skills,id'],
            'skills.*.importance' => ['required_with:skills', 'in:essential,preferred'],
            'skills.*.min_proficiency' => ['required_with:skills', 'integer', 'min:1', 'max:5'],
        ];
    }
}
