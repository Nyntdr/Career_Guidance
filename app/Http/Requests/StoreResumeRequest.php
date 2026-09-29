<?php

namespace App\Http\Requests;

use App\Services\ResumeTextExtractor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supportedExtensions = implode(',', ResumeTextExtractor::SUPPORTED_EXTENSIONS);

        return [
            'resume_file' => [
                'nullable',
                'file',
                "mimes:{$supportedExtensions}",
                'mimetypes:text/plain,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword',
                'max:5120',
            ],
            'content' => [
                'nullable',
                'string',
                'max:50000',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->hasFile('resume_file') && ! $this->filled('content')) {
                    $validator->errors()->add(
                        'resume_file',
                        'Please upload a file or paste resume text.'
                    );
                }
            },
        ];
    }
}
