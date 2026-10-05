<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => ['required', 'string', 'regex:/^[a-zA-Z\s]+$/', 'max:50'],
            'email' => ['required', 'email', "unique:users,email,{$userId}"],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->symbols(), 'max:15', 'confirmed'],
            'role' => ['required', 'string', 'in:user,admin'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.regex' => 'The name field may only contain letters and spaces.',
        ];
    }
}
