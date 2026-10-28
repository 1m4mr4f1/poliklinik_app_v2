<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JadwalPeriksaController extends Controller
{
    private const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function index(): View
    {
        $jadwals = JadwalPeriksa::query()->where('id_dokter', Auth::id())->orderBy('hari')->orderBy('jam_mulai')->paginate(15);

        return view('dokter.jadwal-periksa.index', compact('jadwals'));
    }

    public function create(): View
    {
        return $this->form(new JadwalPeriksa, 'Tambah Jadwal Praktik', route('dokter.jadwal-periksa.store'), 'POST');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->ensureNoOverlap($data['hari'], $data['jam_mulai'], $data['jam_selesai']);
        JadwalPeriksa::create([...$data, 'id_dokter' => Auth::id()]);

        return redirect()->route('dokter.jadwal-periksa.index')->with('success', 'Jadwal praktik berhasil ditambahkan.');
    }

    public function edit(JadwalPeriksa $jadwalPeriksa): View
    {
        $this->ensureOwner($jadwalPeriksa);

        return $this->form($jadwalPeriksa, 'Edit Jadwal Praktik', route('dokter.jadwal-periksa.update', $jadwalPeriksa), 'PUT');
    }

    public function update(Request $request, JadwalPeriksa $jadwalPeriksa): RedirectResponse
    {
        $this->ensureOwner($jadwalPeriksa);
        $data = $this->validated($request);
        $this->ensureNoOverlap($data['hari'], $data['jam_mulai'], $data['jam_selesai'], $jadwalPeriksa->id);
        $jadwalPeriksa->update($data);

        return redirect()->route('dokter.jadwal-periksa.index')->with('success', 'Jadwal praktik berhasil diperbarui.');
    }

    public function destroy(JadwalPeriksa $jadwalPeriksa): RedirectResponse
    {
        $this->ensureOwner($jadwalPeriksa);
        if ($jadwalPeriksa->daftarPolis()->exists()) {
            return back()->with('error', 'Jadwal tidak dapat dihapus karena sudah memiliki pendaftaran pasien.');
        }

        $jadwalPeriksa->delete();

        return back()->with('success', 'Jadwal praktik berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $request->merge([
            'jam_mulai' => is_string($request->input('jam_mulai')) ? substr($request->input('jam_mulai'), 0, 5) : $request->input('jam_mulai'),
            'jam_selesai' => is_string($request->input('jam_selesai')) ? substr($request->input('jam_selesai'), 0, 5) : $request->input('jam_selesai'),
        ]);

        $data = $request->validate([
            'hari' => ['required', 'string', 'in:'.implode(',', self::HARI)],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i'],
        ]);

        if ($data['jam_selesai'] <= $data['jam_mulai']) {
            throw ValidationException::withMessages([
                'jam_selesai' => 'Jam selesai harus setelah jam mulai.',
            ]);
        }

        return $data;
    }

    private function ensureNoOverlap(string $hari, string $mulai, string $selesai, ?int $exceptId = null): void
    {
        $overlap = JadwalPeriksa::query()
            ->where('id_dokter', Auth::id())
            ->where('hari', $hari)
            ->where('jam_mulai', '<', $selesai)
            ->where('jam_selesai', '>', $mulai)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'jam_mulai' => 'Jadwal bertabrakan dengan jadwal praktik lain pada hari tersebut.',
            ]);
        }
    }

    private function ensureOwner(JadwalPeriksa $jadwalPeriksa): void
    {
        abort_unless((int) $jadwalPeriksa->id_dokter === (int) Auth::id(), 404);
    }

    private function form(JadwalPeriksa $jadwal, string $title, string $action, string $method): View
    {
        return view('admin.shared.form', [
            'title' => $title,
            'action' => $action,
            'method' => $method,
            'backUrl' => route('dokter.jadwal-periksa.index'),
            'fields' => [
                ['name' => 'hari', 'label' => 'Hari', 'type' => 'select', 'value' => $jadwal->hari, 'required' => true, 'options' => array_combine(self::HARI, self::HARI)],
                ['name' => 'jam_mulai', 'label' => 'Jam Mulai', 'type' => 'time', 'value' => $jadwal->jam_mulai ? substr((string) $jadwal->jam_mulai, 0, 5) : null, 'required' => true],
                ['name' => 'jam_selesai', 'label' => 'Jam Selesai', 'type' => 'time', 'value' => $jadwal->jam_selesai ? substr((string) $jadwal->jam_selesai, 0, 5) : null, 'required' => true],
            ],
        ]);
    }
}
