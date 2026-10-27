<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadPaymentProofRequest;
use App\Models\Pembayaran;
use App\Models\Periksa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class PembayaranController extends Controller
{
    public function index(): View
    {
        $periksas = Periksa::query()
            ->whereHas('daftarPoli', fn ($query) => $query->where('id_pasien', Auth::id()))
            ->with(['daftarPoli.jadwalPeriksa.dokter.poli', 'pembayaran'])
            ->latest('tgl_periksa')
            ->paginate(15);

        foreach ($periksas as $periksa) {
            if (! $periksa->pembayaran) {
                $periksa->setRelation(
                    'pembayaran',
                    $periksa->pembayaran()->firstOrCreate([], ['status' => 'belum_dibayar']),
                );
            }
        }

        return view('pasien.pembayaran.index', compact('periksas'));
    }

    public function upload(UploadPaymentProofRequest $request, Pembayaran $pembayaran): RedirectResponse
    {
        $pembayaran->loadMissing('periksa.daftarPoli');
        abort_unless((int) $pembayaran->periksa?->daftarPoli?->id_pasien === (int) Auth::id(), 404);

        $path = null;

        try {
            DB::transaction(function () use ($request, $pembayaran, &$path): void {
                $lockedPayment = Pembayaran::query()->whereKey($pembayaran->id)->lockForUpdate()->firstOrFail();
                abort_unless($lockedPayment->status === 'belum_dibayar' && $lockedPayment->bukti_path === null, 422, 'Bukti pembayaran sudah dikirim atau pembayaran telah lunas.');

                $path = $request->file('bukti')->store('bukti-pembayaran/'.Auth::id(), 'local');
                if (! $path) {
                    throw new RuntimeException('Bukti pembayaran gagal disimpan.');
                }

                $lockedPayment->update([
                    'bukti_path' => $path,
                    'status' => 'menunggu_verifikasi',
                ]);
            });
        } catch (Throwable $exception) {
            if ($path && Storage::disk('local')->exists($path) && ! Storage::disk('local')->delete($path)) {
                report(new RuntimeException('Bukti pembayaran gagal dibersihkan setelah transaksi dibatalkan.'));
            }

            throw $exception;
        }

        return back()->with('success', 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi admin.');
    }

    public function proof(Pembayaran $pembayaran): BinaryFileResponse
    {
        $pembayaran->loadMissing('periksa.daftarPoli');
        abort_unless(
            Auth::user()->role === 'admin'
                || (int) $pembayaran->periksa?->daftarPoli?->id_pasien === (int) Auth::id(),
            404,
        );
        abort_unless($pembayaran->bukti_path && Storage::disk('local')->exists($pembayaran->bukti_path), 404);

        return response()->file(
            Storage::disk('local')->path($pembayaran->bukti_path),
            [
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
