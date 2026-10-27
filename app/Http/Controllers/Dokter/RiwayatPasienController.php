<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RiwayatPasienController extends Controller
{
    public function index(): View
    {
        $riwayatPasien = Periksa::query()
            ->whereHas('daftarPoli.jadwalPeriksa', fn ($query) => $query->where('id_dokter', Auth::id()))
            ->with(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli', 'detailPeriksas.obat'])
            ->latest('tgl_periksa')
            ->paginate(20);

        return view('dokter.riwayat-pasien.index', compact('riwayatPasien'));
    }

    public function show(Periksa $periksa): View
    {
        abort_unless(
            $periksa->daftarPoli()
                ->whereHas('jadwalPeriksa', fn ($query) => $query->where('id_dokter', Auth::id()))
                ->exists(),
            404,
        );
        $periksa->load(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli', 'detailPeriksas.obat']);

        return view('dokter.riwayat-pasien.show', compact('periksa'));
    }
}
