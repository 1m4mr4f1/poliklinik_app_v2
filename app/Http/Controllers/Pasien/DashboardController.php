<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $jadwals = JadwalPeriksa::query()
            ->with(['dokter.poli', 'antrianHariIni'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $pendaftaranAktif = DaftarPoli::query()
            ->where('id_pasien', Auth::id())
            ->whereDoesntHave('periksa')
            ->with(['jadwalPeriksa.dokter.poli'])
            ->latest('created_at')
            ->first();

        $nomorDiperiksaAktif = $pendaftaranAktif
            ? $pendaftaranAktif->jadwalPeriksa
                ->antrianHarians()
                ->whereDate('tanggal', $pendaftaranAktif->tanggal_janji)
                ->value('no_antrian_diperiksa')
            : null;

        return view('pasien.dashboard', compact('jadwals', 'pendaftaranAktif', 'nomorDiperiksaAktif'));
    }
}
