<?php

namespace App\Observers;

use App\Models\Periksa;

class PeriksaObserver
{
    public function created(Periksa $periksa): void
    {
        $periksa->pembayaran()->firstOrCreate([], ['status' => 'belum_dibayar']);
    }
}
