<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Obat extends Model
{
    protected $table = 'obat';

    protected $fillable = ['nama_obat', 'kemasan', 'harga', 'stok'];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'stok' => 'integer',
        ];
    }

    public function detailPeriksas()
    {
        return $this->hasMany(DetailPeriksa::class, 'id_obat');
    }
}
