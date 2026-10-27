<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AntrianDiperbarui implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $idJadwal,
        public readonly string $tanggal,
        public readonly int $nomorDipanggil,
        public readonly ?int $nomorDiperiksa = null,
        public readonly int $jumlahBelumDiperiksa = 0,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("antrian.{$this->idJadwal}.{$this->tanggal}")];
    }

    public function broadcastAs(): string
    {
        return 'antrian.diperbarui';
    }

    public function broadcastWith(): array
    {
        return [
            'id_jadwal' => $this->idJadwal,
            'tanggal' => $this->tanggal,
            'nomor_dipanggil' => $this->nomorDipanggil,
            'nomor_diperiksa' => $this->nomorDiperiksa,
            'jumlah_belum_diperiksa' => $this->jumlahBelumDiperiksa,
        ];
    }
}
