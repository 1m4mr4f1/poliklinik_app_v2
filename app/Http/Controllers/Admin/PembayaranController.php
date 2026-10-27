<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function index(): View
    {
        $pembayarans = Pembayaran::query()
            ->where('status', 'menunggu_verifikasi')
            ->with(['periksa.daftarPoli.pasien', 'periksa.daftarPoli.jadwalPeriksa.dokter.poli'])
            ->latest('updated_at')
            ->paginate(15);

        return view('admin.pembayaran.index', compact('pembayarans'));
    }

    public function verify(Pembayaran $pembayaran): RedirectResponse
    {
        $wasVerified = DB::transaction(function () use ($pembayaran): bool {
            $lockedPayment = Pembayaran::query()
                ->whereKey($pembayaran->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayment->status === 'lunas') {
                return false;
            }

            abort_unless(
                $lockedPayment->status === 'menunggu_verifikasi' && $lockedPayment->bukti_path !== null,
                422,
                'Pembayaran belum memiliki bukti yang dapat diverifikasi.',
            );

            $lockedPayment->update([
                'status' => 'lunas',
                'diverifikasi_oleh' => auth()->id(),
                'diverifikasi_pada' => now(),
            ]);

            return true;
        });

        return back()->with(
            'success',
            $wasVerified ? 'Pembayaran berhasil diverifikasi.' : 'Pembayaran ini sudah berstatus lunas.',
        );
    }
}
