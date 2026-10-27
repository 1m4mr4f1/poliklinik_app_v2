<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UploadPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'pasien';
    }

    public function rules(): array
    {
        return [
            'bukti' => ['required', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max('5mb')],
        ];
    }

    public function messages(): array
    {
        return [
            'bukti.required' => 'Pilih foto bukti pembayaran terlebih dahulu.',
            'bukti.image' => 'Bukti pembayaran harus berupa file gambar.',
            'bukti.max' => 'Ukuran foto bukti pembayaran maksimal 5 MB.',
        ];
    }
}
