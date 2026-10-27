<?php

namespace App\Observers;

use App\Models\Pembayaran;
use App\Models\User;
use App\Notifications\PaymentStatusNotification;
use Illuminate\Support\Facades\DB;

class PembayaranObserver
{
    public function created(Pembayaran $pembayaran): void
    {
        if ($pembayaran->bukti_path) {
            $this->notifyDoctorOfUpload($pembayaran);
        }
    }

    public function updated(Pembayaran $pembayaran): void
    {
        if ($pembayaran->wasChanged('bukti_path') && $pembayaran->bukti_path) {
            $this->notifyDoctorOfUpload($pembayaran);
        }

        if ($pembayaran->wasChanged('status') && $pembayaran->status === 'lunas') {
            $this->notifyPatientOfVerification($pembayaran);
        }
    }

    private function notifyDoctorOfUpload(Pembayaran $pembayaran): void
    {
        $pembayaran->loadMissing('periksa.daftarPoli.jadwalPeriksa.dokter');
        $dokter = $pembayaran->periksa?->daftarPoli?->jadwalPeriksa?->dokter;

        if (! $dokter) {
            return;
        }

        $this->notifyOnce(
            $pembayaran,
            $dokter,
            "payment:{$pembayaran->id}:proof-uploaded",
            'bukti_pembayaran',
            'Bukti pembayaran baru telah diunggah untuk pemeriksaan.',
            route('dokter.dashboard'),
        );
    }

    private function notifyPatientOfVerification(Pembayaran $pembayaran): void
    {
        $pembayaran->loadMissing('periksa.daftarPoli.pasien');
        $pasien = $pembayaran->periksa?->daftarPoli?->pasien;

        if (! $pasien) {
            return;
        }

        $this->notifyOnce(
            $pembayaran,
            $pasien,
            "payment:{$pembayaran->id}:verified",
            'pembayaran_lunas',
            'Pembayaran Anda telah diverifikasi dan dinyatakan lunas.',
            route('pasien.dashboard'),
        );
    }

    private function notifyOnce(
        Pembayaran $pembayaran,
        User $recipient,
        string $eventKey,
        string $event,
        string $message,
        string $url,
    ): void {
        DB::transaction(function () use ($pembayaran, $recipient, $eventKey, $event, $message, $url): void {
            Pembayaran::query()->whereKey($pembayaran->id)->lockForUpdate()->firstOrFail();

            $alreadySent = $recipient->notifications()
                ->where('type', PaymentStatusNotification::class)
                ->where('data->event_key', $eventKey)
                ->exists();

            if ($alreadySent) {
                return;
            }

            $recipient->notify(new PaymentStatusNotification(
                $eventKey,
                (int) $pembayaran->id,
                $event,
                $message,
                $url,
            ));
        });
    }
}
