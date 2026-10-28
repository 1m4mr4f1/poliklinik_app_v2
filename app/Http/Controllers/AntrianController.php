<?php

namespace App\Http\Controllers;

use App\Events\AntrianDiperbarui;
use App\Models\AntrianHarian;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AntrianController extends Controller
{
    public function panggilBerikutnya(JadwalPeriksa $jadwalPeriksa): RedirectResponse
    {
        abort_unless(
            Auth::user()->role === 'admin' || (int) $jadwalPeriksa->id_dokter === (int) Auth::id(),
            403,
        );

        $hariIni = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ][today()->dayOfWeek];

        abort_unless($jadwalPeriksa->hari === $hariIni, 422, 'Antrian hanya dapat dipanggil pada hari praktik.');

        $nomorDipanggil = DB::transaction(function () use ($jadwalPeriksa) {
            $jadwal = JadwalPeriksa::query()
                ->whereKey($jadwalPeriksa->id)
                ->lockForUpdate()
                ->firstOrFail();

            $antrianHarian = AntrianHarian::query()
                ->where('id_jadwal', $jadwal->id)
                ->whereDate('tanggal', today())
                ->lockForUpdate()
                ->first();

            $nomorSaatIni = $antrianHarian?->no_antrian_dipanggil;
            $nomorDiperiksa = $antrianHarian?->no_antrian_diperiksa;

            if ($nomorSaatIni !== null && $nomorDiperiksa !== $nomorSaatIni) {
                $antrianMasihDiperiksa = DaftarPoli::query()
                    ->where('id_jadwal', $jadwal->id)
                    ->whereDate('tanggal_janji', today())
                    ->where('no_antrian', $nomorSaatIni)
                    ->whereDoesntHave('periksa')
                    ->exists();

                if ($antrianMasihDiperiksa) {
                    return null;
                }
            }

            $pendaftaranBerikutnya = DaftarPoli::query()
                ->where('id_jadwal', $jadwal->id)
                ->whereDate('tanggal_janji', today())
                ->whereDoesntHave('periksa')
                ->when(
                    $nomorSaatIni !== null,
                    fn ($query) => $query->where('no_antrian', '>', $nomorSaatIni),
                )
                ->orderBy('no_antrian')
                ->first();

            if (! $pendaftaranBerikutnya) {
                return null;
            }

            $antrianHarian ??= new AntrianHarian([
                'id_jadwal' => $jadwal->id,
                'tanggal' => today(),
            ]);
            $antrianHarian->no_antrian_dipanggil = $pendaftaranBerikutnya->no_antrian;
            $antrianHarian->save();

            return [
                'dipanggil' => (int) $antrianHarian->no_antrian_dipanggil,
                'diperiksa' => $antrianHarian->no_antrian_diperiksa,
            ];
        });

        if ($nomorDipanggil === null) {
            return back()->with(
                'error',
                'Tidak ada antrian berikutnya yang dapat dipanggil. Pastikan pasien sebelumnya sudah diperiksa.',
            );
        }

        try {
            AntrianDiperbarui::dispatch(
                (int) $jadwalPeriksa->id,
                today()->toDateString(),
                $nomorDipanggil['dipanggil'],
                $nomorDipanggil['diperiksa'],
                DaftarPoli::query()
                    ->where('id_jadwal', $jadwalPeriksa->id)
                    ->whereDate('tanggal_janji', today())
                    ->whereDoesntHave('periksa')
                    ->count(),
            );
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', "Nomor antrian {$nomorDipanggil['dipanggil']} berhasil dipanggil.");
    }
}
