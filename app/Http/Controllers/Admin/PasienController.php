<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasienController extends Controller
{
    public function index(): View
    {
        $pasiens = User::query()->where('role', 'pasien')->orderBy('nama')->paginate(15);

        return view('admin.shared.index', [
            'title' => 'Manajemen Pasien',
            'createRoute' => route('admin.pasien.create'),
            'columns' => ['Nama', 'No. RM', 'Email', 'No. HP'],
            'items' => $pasiens->through(fn (User $pasien) => [
                'values' => [$pasien->nama, $pasien->no_rm, $pasien->email, $pasien->no_hp],
                'editUrl' => route('admin.pasien.edit', $pasien),
                'deleteUrl' => route('admin.pasien.destroy', $pasien),
            ]),
        ]);
    }

    public function create(): View
    {
        return $this->form(new User, 'Tambah Pasien', route('admin.pasien.store'), 'POST');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:255'],
            'no_ktp' => ['required', 'string', 'max:16', 'unique:users,no_ktp'],
            'no_hp' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
        ]);

        DB::transaction(function () use ($validated): void {
            $pasien = User::create([...$validated, 'role' => 'pasien']);
            $pasien->update([
                'no_rm' => now()->format('Ym').'-'.str_pad((string) $pasien->id, 3, '0', STR_PAD_LEFT),
            ]);
        });

        return redirect()->route('admin.pasien.index')->with('success', 'Pasien berhasil ditambahkan.');
    }

    public function edit(User $pasien): View
    {
        $this->ensurePatient($pasien);

        return $this->form($pasien, 'Edit Pasien', route('admin.pasien.update', $pasien), 'PUT');
    }

    public function update(Request $request, User $pasien): RedirectResponse
    {
        $this->ensurePatient($pasien);
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:255'],
            'no_ktp' => ['required', 'string', 'max:16', Rule::unique('users', 'no_ktp')->ignore($pasien->id)],
            'no_hp' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pasien->id)],
            'password' => ['nullable', 'string', Password::min(8)],
        ]);

        if (blank($validated['password'])) {
            unset($validated['password']);
        }

        $pasien->update($validated);

        return redirect()->route('admin.pasien.index')->with('success', 'Data pasien berhasil diperbarui.');
    }

    public function destroy(User $pasien): RedirectResponse
    {
        $this->ensurePatient($pasien);
        if ($pasien->pendaftaranPoli()->exists()) {
            return back()->with('error', 'Pasien tidak dapat dihapus karena memiliki riwayat pendaftaran.');
        }

        $pasien->delete();

        return back()->with('success', 'Pasien berhasil dihapus.');
    }

    private function form(User $pasien, string $title, string $action, string $method): View
    {
        return view('admin.shared.form', [
            'title' => $title,
            'action' => $action,
            'method' => $method,
            'backUrl' => route('admin.pasien.index'),
            'fields' => [
                ['name' => 'nama', 'label' => 'Nama Lengkap', 'value' => $pasien->nama, 'required' => true],
                ['name' => 'alamat', 'label' => 'Alamat', 'value' => $pasien->alamat, 'required' => true],
                ['name' => 'no_ktp', 'label' => 'Nomor KTP', 'value' => $pasien->no_ktp, 'required' => true, 'max' => 16],
                ['name' => 'no_hp', 'label' => 'Nomor HP', 'value' => $pasien->no_hp, 'required' => true, 'max' => 20],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $pasien->email, 'required' => true],
                ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => $method === 'POST', 'help' => $method === 'PUT' ? 'Kosongkan jika tidak ingin mengganti password.' : 'Minimal 8 karakter.'],
            ],
        ]);
    }

    private function ensurePatient(User $pasien): void
    {
        abort_unless($pasien->role === 'pasien', 404);
    }
}
