<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'id_periksa',
        'status',
        'bukti_path',
        'diverifikasi_oleh',
        'diverifikasi_pada',
    ];

    protected function casts(): array
    {
        return [
            'diverifikasi_pada' => 'datetime',
        ];
    }

    public function periksa()
    {
        return $this->belongsTo(Periksa::class, 'id_periksa');
    }

    public function diverifikasiOleh()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
