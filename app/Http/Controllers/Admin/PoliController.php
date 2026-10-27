<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poli;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PoliController extends Controller
{
    public function index(): View
    {
        $polis = Poli::query()->withCount('dokters')->orderBy('nama_poli')->paginate(15);

        return view('admin.shared.index', [
            'title' => 'Manajemen Poli',
            'createRoute' => route('admin.polis.create'),
            'columns' => ['Nama Poli', 'Keterangan', 'Jumlah Dokter'],
            'items' => $polis->through(fn (Poli $poli) => [
                'values' => [$poli->nama_poli, $poli->keterangan ?: '—', $poli->dokters_count],
                'editUrl' => route('admin.polis.edit', $poli),
                'deleteUrl' => route('admin.polis.destroy', $poli),
            ]),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Poli, 'Tambah Poli', route('admin.polis.store'), 'POST');
    }

    public function store(Request $request): RedirectResponse
    {
        Poli::create($request->validate([
            'nama_poli' => ['required', 'string', 'max:25'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]));

        return redirect()->route('admin.polis.index')->with('success', 'Poli berhasil ditambahkan.');
    }

    public function edit(Poli $poli): View
    {
        return $this->form($poli, 'Edit Poli', route('admin.polis.update', $poli), 'PUT');
    }

    public function update(Request $request, Poli $poli): RedirectResponse
    {
        $poli->update($request->validate([
            'nama_poli' => ['required', 'string', 'max:25'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]));

        return redirect()->route('admin.polis.index')->with('success', 'Poli berhasil diperbarui.');
    }

    public function destroy(Poli $poli): RedirectResponse
    {
        if ($poli->dokters()->exists()) {
            return back()->with('error', 'Poli tidak dapat dihapus selama masih digunakan oleh dokter.');
        }

        $poli->delete();

        return back()->with('success', 'Poli berhasil dihapus.');
    }

    private function form(Poli $poli, string $title, string $action, string $method): View
    {
        return view('admin.shared.form', [
            'title' => $title,
            'action' => $action,
            'method' => $method,
            'backUrl' => route('admin.polis.index'),
            'fields' => [
                ['name' => 'nama_poli', 'label' => 'Nama Poli', 'value' => $poli->nama_poli, 'required' => true, 'max' => 25],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => $poli->keterangan],
            ],
        ]);
    }
}
