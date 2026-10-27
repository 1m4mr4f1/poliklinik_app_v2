<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RiwayatPendaftaranController extends Controller
{
    public function index(): View
    {
        $pendaftarans = DaftarPoli::query()
            ->where('id_pasien', Auth::id())
            ->with([
                'jadwalPeriksa.dokter.poli',
                'periksa',
            ])
            ->latest('created_at')
            ->paginate(15);

        return view('pasien.riwayat.index', compact('pendaftarans'));
    }

    public function show(DaftarPoli $daftarPoli): View
    {
        abort_unless((int) $daftarPoli->id_pasien === (int) Auth::id(), 404);

        $daftarPoli->load([
            'jadwalPeriksa.dokter.poli',
            'pasien',
            'periksa.detailPeriksas.obat',
        ]);
        abort_unless($daftarPoli->periksa, 404);

        return view('pasien.riwayat.show', [
            'pendaftaran' => $daftarPoli,
            'periksa' => $daftarPoli->periksa,
        ]);
    }
}
