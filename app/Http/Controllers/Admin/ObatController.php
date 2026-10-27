<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ObatController extends Controller
{
    public function index(): View
    {
        $obats = Obat::query()->orderBy('nama_obat')->paginate(15);

        return view('admin.shared.index', [
            'title' => 'Manajemen Obat',
            'createRoute' => route('admin.obat.create'),
            'columns' => ['Nama Obat', 'Kemasan', 'Stok', 'Harga'],
            'items' => $obats->through(fn (Obat $obat) => [
                'values' => [$obat->nama_obat, $obat->kemasan ?: '—', $obat->stok, 'Rp'.number_format($obat->harga, 0, ',', '.')],
                'editUrl' => route('admin.obat.edit', $obat),
                'deleteUrl' => route('admin.obat.destroy', $obat),
            ]),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Obat, 'Tambah Obat', route('admin.obat.store'), 'POST');
    }

    public function store(Request $request): RedirectResponse
    {
        Obat::create($request->validate([
            'nama_obat' => ['required', 'string', 'max:255'],
            'kemasan' => ['nullable', 'string', 'max:35'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
        ]));

        return redirect()->route('admin.obat.index')->with('success', 'Obat berhasil ditambahkan.');
    }

    public function edit(Obat $obat): View
    {
        return $this->form($obat, 'Edit Obat', route('admin.obat.update', $obat), 'PUT');
    }

    public function update(Request $request, Obat $obat): RedirectResponse
    {
        $obat->update($request->validate([
            'nama_obat' => ['required', 'string', 'max:255'],
            'kemasan' => ['nullable', 'string', 'max:35'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
        ]));

        return redirect()->route('admin.obat.index')->with('success', 'Data obat berhasil diperbarui.');
    }

    public function destroy(Obat $obat): RedirectResponse
    {
        if ($obat->detailPeriksas()->exists()) {
            return back()->with('error', 'Obat tidak dapat dihapus karena sudah tercatat pada pemeriksaan.');
        }

        $obat->delete();

        return back()->with('success', 'Obat berhasil dihapus.');
    }

    private function form(Obat $obat, string $title, string $action, string $method): View
    {
        return view('admin.shared.form', [
            'title' => $title,
            'action' => $action,
            'method' => $method,
            'backUrl' => route('admin.obat.index'),
            'fields' => [
                ['name' => 'nama_obat', 'label' => 'Nama Obat', 'value' => $obat->nama_obat, 'required' => true],
                ['name' => 'kemasan', 'label' => 'Kemasan', 'value' => $obat->kemasan, 'max' => 35],
                ['name' => 'stok', 'label' => 'Stok', 'type' => 'number', 'value' => $obat->stok ?? 0, 'required' => true, 'min' => 0],
                ['name' => 'harga', 'label' => 'Harga (Rp)', 'type' => 'number', 'value' => $obat->harga, 'required' => true, 'min' => 0],
            ],
        ]);
    }
}
