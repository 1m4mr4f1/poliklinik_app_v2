<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class DokterController extends Controller
{
    public function index(): View
    {
        $dokters = User::query()->where('role', 'dokter')->with('poli')->orderBy('nama')->paginate(15);

        return view('admin.shared.index', [
            'title' => 'Manajemen Dokter',
            'createRoute' => route('admin.dokter.create'),
            'exportRoute' => route('admin.export.dokter'),
            'columns' => ['Nama', 'Poli', 'Email', 'No. HP'],
            'items' => $dokters->through(fn (User $dokter) => [
                'values' => [$dokter->nama, $dokter->poli?->nama_poli ?? 'Belum ditentukan', $dokter->email, $dokter->no_hp],
                'editUrl' => route('admin.dokter.edit', $dokter),
                'deleteUrl' => route('admin.dokter.destroy', $dokter),
            ]),
        ]);
    }

    public function create(): View
    {
        return $this->form(new User, 'Tambah Dokter', route('admin.dokter.store'), 'POST');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:255'],
            'no_ktp' => ['required', 'string', 'max:16', 'unique:users,no_ktp'],
            'no_hp' => ['required', 'string', 'max:20'],
            'id_poli' => ['required', 'integer', 'exists:poli,id'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
        ]);
        $validated['role'] = 'dokter';

        User::create($validated);

        return redirect()->route('admin.dokter.index')->with('success', 'Dokter berhasil ditambahkan.');
    }

    public function edit(User $dokter): View
    {
        $this->ensureDoctor($dokter);

        return $this->form($dokter, 'Edit Dokter', route('admin.dokter.update', $dokter), 'PUT');
    }

    public function update(Request $request, User $dokter): RedirectResponse
    {
        $this->ensureDoctor($dokter);
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:255'],
            'no_ktp' => ['required', 'string', 'max:16', Rule::unique('users', 'no_ktp')->ignore($dokter->id)],
            'no_hp' => ['required', 'string', 'max:20'],
            'id_poli' => ['required', 'integer', 'exists:poli,id'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($dokter->id)],
            'password' => ['nullable', 'string', Password::min(8)],
        ]);

        if (blank($validated['password'])) {
            unset($validated['password']);
        }

        $dokter->update($validated);

        return redirect()->route('admin.dokter.index')->with('success', 'Data dokter berhasil diperbarui.');
    }

    public function destroy(User $dokter): RedirectResponse
    {
        $this->ensureDoctor($dokter);
        if ($dokter->jadwalPeriksas()->whereHas('daftarPolis')->exists()) {
            return back()->with('error', 'Dokter tidak dapat dihapus karena sudah memiliki riwayat pendaftaran pasien.');
        }

        DB::transaction(function () use ($dokter): void {
            $dokter->jadwalPeriksas()->delete();
            $dokter->delete();
        });

        return back()->with('success', 'Dokter berhasil dihapus.');
    }

    private function form(User $dokter, string $title, string $action, string $method): View
    {
        return view('admin.shared.form', [
            'title' => $title,
            'action' => $action,
            'method' => $method,
            'backUrl' => route('admin.dokter.index'),
            'fields' => [
                ['name' => 'nama', 'label' => 'Nama Lengkap', 'value' => $dokter->nama, 'required' => true],
                ['name' => 'alamat', 'label' => 'Alamat', 'value' => $dokter->alamat, 'required' => true],
                ['name' => 'no_ktp', 'label' => 'Nomor KTP', 'value' => $dokter->no_ktp, 'required' => true, 'max' => 16],
                ['name' => 'no_hp', 'label' => 'Nomor HP', 'value' => $dokter->no_hp, 'required' => true, 'max' => 20],
                ['name' => 'id_poli', 'label' => 'Poli', 'type' => 'select', 'value' => $dokter->id_poli, 'required' => true, 'options' => Poli::query()->orderBy('nama_poli')->pluck('nama_poli', 'id')->all()],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $dokter->email, 'required' => true],
                ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => $method === 'POST', 'help' => $method === 'PUT' ? 'Kosongkan jika tidak ingin mengganti password.' : 'Minimal 8 karakter.'],
            ],
        ]);
    }

    private function ensureDoctor(User $dokter): void
    {
        abort_unless($dokter->role === 'dokter', 404);
    }
}
