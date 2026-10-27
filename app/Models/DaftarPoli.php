<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaftarPoli extends Model
{
    protected $table = 'daftar_poli';

    protected $fillable = [
        'id_pasien',
        'id_jadwal',
        'tanggal_janji',
        'keluhan',
        'no_antrian',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_janji' => 'date',
        ];
    }

    public function pasien()
    {
        return $this->belongsTo(User::class, 'id_pasien');
    }

    public function jadwalPeriksa()
    {
        return $this->belongsTo(JadwalPeriksa::class, 'id_jadwal');
    }

    public function periksas()
    {
        return $this->hasMany(Periksa::class, 'id_daftar_poli');
    }

    public function periksa()
    {
        return $this->hasOne(Periksa::class, 'id_daftar_poli');
    }
}
