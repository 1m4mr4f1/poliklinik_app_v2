<?php

namespace App\Http\Controllers;

use App\Exports\TableExport;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function dokter(): BinaryFileResponse
    {
        $rows = User::query()
            ->where('role', 'dokter')
            ->with('poli')
            ->orderBy('nama')
            ->get()
            ->map(fn (User $dokter) => [
                $dokter->nama,
                $dokter->email,
                $dokter->no_ktp,
                $dokter->no_hp,
                $dokter->alamat,
                $dokter->poli?->nama_poli,
            ])->all();

        return $this->download('dokter.xlsx', ['Nama', 'Email', 'No. KTP', 'No. HP', 'Alamat', 'Poli'], $rows);
    }

    public function pasien(): BinaryFileResponse
    {
        $rows = User::query()
            ->where('role', 'pasien')
            ->orderBy('nama')
            ->get()
            ->map(fn (User $pasien) => [
                $pasien->nama,
                $pasien->no_rm,
                $pasien->email,
                $pasien->no_ktp,
                $pasien->no_hp,
                $pasien->alamat,
            ])->all();

        return $this->download('pasien.xlsx', ['Nama', 'No. Rekam Medis', 'Email', 'No. KTP', 'No. HP', 'Alamat'], $rows);
    }

    public function obat(): BinaryFileResponse
    {
        $rows = Obat::query()
            ->orderBy('nama_obat')
            ->get()
            ->map(fn (Obat $obat) => [
                $obat->nama_obat,
                $obat->kemasan,
                $obat->stok,
                $obat->harga,
            ])->all();

        return $this->download('obat.xlsx', ['Nama Obat', 'Kemasan', 'Stok', 'Harga'], $rows);
    }

    public function jadwal(): BinaryFileResponse
    {
        $rows = JadwalPeriksa::query()
            ->where('id_dokter', Auth::id())
            ->with('dokter.poli')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn (JadwalPeriksa $jadwal) => [
                $jadwal->dokter?->poli?->nama_poli,
                $jadwal->dokter?->nama,
                $jadwal->hari,
                $jadwal->jam_mulai,
                $jadwal->jam_selesai,
            ])->all();

        return $this->download('jadwal-periksa.xlsx', ['Poli', 'Dokter', 'Hari', 'Jam Mulai', 'Jam Selesai'], $rows);
    }

    public function riwayat(): BinaryFileResponse
    {
        $dokterId = Auth::id();
        $rows = Periksa::query()
            ->whereHas('daftarPoli.jadwalPeriksa', fn ($query) => $query->where('id_dokter', $dokterId))
            ->with(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli'])
            ->latest('tgl_periksa')
            ->get()
            ->map(fn (Periksa $periksa) => [
                $periksa->daftarPoli?->pasien?->nama,
                $periksa->daftarPoli?->pasien?->no_rm,
                $periksa->daftarPoli?->jadwalPeriksa?->dokter?->poli?->nama_poli,
                $periksa->daftarPoli?->tanggal_janji?->toDateString(),
                $periksa->daftarPoli?->no_antrian,
                $periksa->daftarPoli?->keluhan,
                $periksa->tgl_periksa?->toDateTimeString(),
                $periksa->catatan,
                $periksa->biaya_periksa,
            ])->all();

        return $this->download(
            'riwayat-pasien.xlsx',
            ['Nama Pasien', 'No. RM', 'Poli', 'Tanggal Jadwal', 'No. Antrian', 'Keluhan', 'Tanggal Periksa', 'Catatan', 'Biaya'],
            $rows,
        );
    }

    private function download(string $fileName, array $headings, array $rows): BinaryFileResponse
    {
        return Excel::download(new TableExport($headings, $rows), $fileName);
    }
}
