<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', 'different:current_password', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Password lama tidak sesuai.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ];
    }
}
