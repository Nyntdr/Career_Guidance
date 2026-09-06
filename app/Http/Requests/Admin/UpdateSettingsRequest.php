<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:255'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'currency_position' => ['required', Rule::in(['before', 'after'])],
            'ats_weights' => ['required', 'array'],
            'ats_weights.keyword' => ['required', 'integer', 'min:0', 'max:100'],
            'ats_weights.structure' => ['required', 'integer', 'min:0', 'max:100'],
            'ats_weights.formatting' => ['required', 'integer', 'min:0', 'max:100'],
            'ats_weights.readability' => ['required', 'integer', 'min:0', 'max:100'],
            'top_matches' => ['required', 'integer', 'min:1', 'max:50'],
            'admin_page_size' => ['required', 'integer', 'min:5', 'max:100'],
        ];
    }

    public function after(): array
    {
        return [
            function () {
                $weights = $this->input('ats_weights', []);

                $sum = collect($weights)
                    ->filter(fn ($v) => is_numeric($v))
                    ->sum(fn ($v) => (int) $v);

                if ($sum !== 100) {
                    $this->errors()->add('ats_weights', 'ATS scan weights must sum to 100.');
                }
            },
        ];
    }
}