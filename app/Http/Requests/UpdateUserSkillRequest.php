<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserSkillRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'proficiency_level' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
