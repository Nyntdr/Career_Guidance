<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoadmapProgressUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:not_started,in_progress,completed'],
        ];
    }
}
