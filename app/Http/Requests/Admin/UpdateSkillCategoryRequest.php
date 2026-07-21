<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSkillCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('skill_category');

        return [
            'name' => ['required', 'string', 'max:255', "unique:skill_categories,name,{$id}"],
        ];
    }
}
