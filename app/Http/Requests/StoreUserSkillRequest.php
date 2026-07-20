<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserSkillRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'skill_name' => ['required', 'string', 'max:255'],
            'proficiency_level' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
