<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
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
            ->where('hari', $hariIni)
            ->with(['dokter.poli', 'antrianHariIni'])
            ->withCount([
                'daftarPolis as menunggu_diperiksa_count' => fn ($query) => $query
                    ->whereDate('tanggal_janji', today())
                    ->whereDoesntHave('periksa'),
            ])
            ->orderBy('jam_mulai')
            ->get();

        return view('admin.dashboard', compact('jadwals', 'hariIni'));
    }
}
