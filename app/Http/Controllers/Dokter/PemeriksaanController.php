<?php

namespace App\Http\Controllers\Dokter;

use App\Events\AntrianDiperbarui;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePemeriksaanRequest;
use App\Models\AntrianHarian;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PemeriksaanController extends Controller
{
    private const BIAYA_JASA_DOKTER = 150_000;

    public function index(): View
    {
        $daftarPasien = DaftarPoli::query()
            ->whereHas('jadwalPeriksa', fn ($query) => $query->where('id_dokter', Auth::id()))
            ->with(['pasien', 'jadwalPeriksa', 'periksa'])
            ->whereDate('tanggal_janji', today())
            ->orderBy('no_antrian')
            ->paginate(20);

        return view('dokter.pemeriksaan.index', compact('daftarPasien'));
    }

    public function create(DaftarPoli $daftarPoli): View
    {
        $this->ensureCalledPatient($daftarPoli);
        $daftarPoli->load(['pasien', 'jadwalPeriksa.dokter.poli']);
        $obats = Obat::query()->orderBy('nama_obat')->get();

        return view('dokter.pemeriksaan.create', compact('daftarPoli', 'obats'));
    }

    public function store(StorePemeriksaanRequest $request, DaftarPoli $daftarPoli): RedirectResponse
    {
        $obatIds = $request->validated('obat_ids', []);

        $nomorDiperiksa = DB::transaction(function () use ($request, $daftarPoli, $obatIds): int {
            $lockedRegistration = DaftarPoli::query()
                ->whereKey($daftarPoli->id)
                ->lockForUpdate()
                ->firstOrFail();
            $this->ensureCalledPatient($lockedRegistration);

            $biayaObat = Obat::query()->whereIn('id', $obatIds)->sum('harga');
            $periksa = Periksa::create([
                'id_daftar_poli' => $lockedRegistration->id,
                'tgl_periksa' => now(),
                'catatan' => $request->validated('catatan'),
                'biaya_periksa' => $biayaObat + self::BIAYA_JASA_DOKTER,
            ]);

            foreach ($obatIds as $obatId) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $obatId,
                ]);
            }

            $antrianHarian = AntrianHarian::query()
                ->where('id_jadwal', $lockedRegistration->id_jadwal)
                ->whereDate('tanggal', today())
                ->lockForUpdate()
                ->firstOrFail();
            $antrianHarian->no_antrian_diperiksa = (int) $lockedRegistration->no_antrian;
            $antrianHarian->save();

            return (int) $antrianHarian->no_antrian_diperiksa;
        });

        AntrianDiperbarui::dispatch(
            (int) $daftarPoli->id_jadwal,
            today()->toDateString(),
            (int) $daftarPoli->no_antrian,
            $nomorDiperiksa,
            DaftarPoli::query()
                ->where('id_jadwal', $daftarPoli->id_jadwal)
                ->whereDate('tanggal_janji', today())
                ->whereDoesntHave('periksa')
                ->count(),
        );

        return redirect()->route('dokter.dashboard')->with('success', 'Pemeriksaan selesai dan tagihan pasien telah dibuat.');
    }

    private function ensureCalledPatient(DaftarPoli $daftarPoli): void
    {
        $daftarPoli->loadMissing(['jadwalPeriksa', 'periksa']);

        abort_unless(
            (int) $daftarPoli->jadwalPeriksa?->id_dokter === (int) Auth::id(),
            403,
        );
        abort_unless($daftarPoli->tanggal_janji->isToday(), 404);
        abort_if($daftarPoli->periksa, 404);

        $nomorDipanggil = AntrianHarian::query()
            ->where('id_jadwal', $daftarPoli->id_jadwal)
            ->whereDate('tanggal', today())
            ->value('no_antrian_dipanggil');

        abort_unless((int) $nomorDipanggil === (int) $daftarPoli->no_antrian, 403);
    }
}
