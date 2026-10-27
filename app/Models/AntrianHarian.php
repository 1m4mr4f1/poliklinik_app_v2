<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AntrianHarian extends Model
{
    protected $table = 'antrian_harian';

    protected $fillable = ['id_jadwal', 'tanggal', 'no_antrian_dipanggil', 'no_antrian_diperiksa'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function jadwalPeriksa()
    {
        return $this->belongsTo(JadwalPeriksa::class, 'id_jadwal');
    }
}
