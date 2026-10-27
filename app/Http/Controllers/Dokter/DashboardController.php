<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $hariIni = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ][now()->dayOfWeek];

        $jadwals = JadwalPeriksa::query()
            ->where('id_dokter', Auth::id())
            ->where('hari', $hariIni)
            ->with([
                'dokter.poli',
                'antrianHariIni',
                'daftarPolis' => fn ($query) => $query
                    ->whereDate('tanggal_janji', today())
                    ->whereDoesntHave('periksa'),
                'daftarPolis.periksa',
            ])
            ->withCount([
                'daftarPolis as menunggu_diperiksa_count' => fn ($query) => $query
                    ->whereDate('tanggal_janji', today())
                    ->whereDoesntHave('periksa'),
            ])
            ->orderBy('jam_mulai')
            ->get();

        return view('dokter.dashboard', compact('jadwals', 'hariIni'));
    }
}
