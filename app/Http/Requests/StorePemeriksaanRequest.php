<?php

namespace App\Http\Requests;

use App\Models\DaftarPoli;
use Illuminate\Foundation\Http\FormRequest;

class StorePemeriksaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pendaftaran = $this->route('daftarPoli');

        return $this->user()?->role === 'dokter'
            && $pendaftaran instanceof DaftarPoli
            && (int) $pendaftaran->jadwalPeriksa()->value('id_dokter') === (int) $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'catatan' => ['nullable', 'string', 'max:5000'],
            'biaya_dokter' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'obat_ids' => ['nullable', 'array'],
            'obat_ids.*' => ['integer', 'distinct', 'exists:obat,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'biaya_dokter' => 'biaya jasa dokter',
        ];
    }
}
