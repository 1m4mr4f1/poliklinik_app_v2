<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PendaftaranController extends Controller
{
    public function create(): View
    {
        $jadwals = JadwalPeriksa::query()
            ->with(['dokter.poli'])
            ->whereHas('dokter', fn ($query) => $query->whereNotNull('id_poli'))
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return view('pasien.pendaftaran.create', compact('jadwals'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_jadwal' => ['required', 'integer', 'exists:jadwal_periksa,id'],
            'tanggal_janji' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'keluhan' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($validated): void {
            $jadwal = JadwalPeriksa::query()
                ->whereKey($validated['id_jadwal'])
                ->lockForUpdate()
                ->firstOrFail();
            $jadwal->load('dokter');

            if ($jadwal->dokter?->role !== 'dokter' || ! $jadwal->dokter->id_poli) {
                throw ValidationException::withMessages([
                    'id_jadwal' => 'Jadwal ini belum terhubung dengan dokter dan poli yang aktif.',
                ]);
            }

            $hariTanggal = [
                0 => 'Minggu',
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
            ][now()->parse($validated['tanggal_janji'])->dayOfWeek];

            if ($jadwal->hari !== $hariTanggal) {
                throw ValidationException::withMessages([
                    'tanggal_janji' => 'Tanggal janji harus sesuai dengan hari praktik dokter.',
                ]);
            }

            $pasien = User::query()->whereKey(Auth::id())->lockForUpdate()->firstOrFail();

            $sudahAdaPendaftaranAktif = DaftarPoli::query()
                ->where('id_pasien', $pasien->id)
                ->whereDoesntHave('periksa')
                ->exists();

            if ($sudahAdaPendaftaranAktif) {
                throw ValidationException::withMessages([
                    'id_jadwal' => 'Selesaikan pemeriksaan yang masih aktif sebelum mendaftar kembali.',
                ]);
            }

            $nomorAntrianBerikutnya = (int) DaftarPoli::query()
                ->where('id_jadwal', $jadwal->id)
                ->whereDate('tanggal_janji', $validated['tanggal_janji'])
                ->max('no_antrian') + 1;

            DaftarPoli::create([
                'id_pasien' => $pasien->id,
                'id_jadwal' => $jadwal->id,
                'tanggal_janji' => $validated['tanggal_janji'],
                'keluhan' => $validated['keluhan'],
                'no_antrian' => $nomorAntrianBerikutnya,
            ]);
        });

        return redirect()->route('pasien.dashboard')->with('success', 'Pendaftaran poli berhasil dibuat.');
    }
}
