<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCareerRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'unique:career_roles,title'],
            'description' => ['required', 'string'],
            'typical_seniority' => ['required', 'string', 'max:255'],
            'avg_salary_range' => ['required', 'array'],
            'avg_salary_range.min' => ['required', 'integer', 'min:0'],
            'avg_salary_range.max' => ['required', 'integer', 'min:0'],
            'growth_outlook' => ['required', 'string', 'max:255'],
            'skills' => ['required', 'array', 'min:1'],
            'skills.*.id' => ['required', 'exists:skills,id'],
            'skills.*.importance' => ['required', 'in:essential,preferred'],
            'skills.*.min_proficiency' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
