<x-layouts.app title="Detail Riwayat Pasien">
    <div class="mx-auto max-w-3xl space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-clinic-text">Detail Riwayat Pasien</h1>
                <p class="mt-1 text-sm text-clinic-secondary font-medium">
                    {{ $periksa->daftarPoli?->pasien?->nama }} &middot; No. RM {{ $periksa->daftarPoli?->pasien?->no_rm }}
                </p>
            </div>
            <x-ui.button href="{{ route('dokter.riwayat-pasien.index') }}" variant="secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </x-ui.button>
        </div>

        <x-ui.card class="space-y-4">
            <dl class="grid gap-4 sm:grid-cols-2 rounded-control border border-clinic-border bg-clinic-background p-4 text-xs">
                <div>
                    <dt class="text-clinic-muted">Poli / Dokter</dt>
                    <dd class="font-semibold text-clinic-text mt-0.5">{{ $periksa->daftarPoli?->jadwalPeriksa?->dokter?->poli?->nama_poli }} / {{ $periksa->daftarPoli?->jadwalPeriksa?->dokter?->nama }}</dd>
                </div>
                <div>
                    <dt class="text-clinic-muted">Tanggal Periksa</dt>
                    <dd class="font-semibold text-clinic-text mt-0.5">{{ $periksa->tgl_periksa->translatedFormat('d F Y, H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-clinic-muted">Nomor Antrian</dt>
                    <dd class="font-semibold text-clinic-text mt-0.5">#{{ $periksa->daftarPoli?->no_antrian }}</dd>
                </div>
                <div>
                    <dt class="text-clinic-muted">Keluhan</dt>
                    <dd class="font-semibold text-clinic-text mt-0.5">{{ $periksa->daftarPoli?->keluhan ?: '-' }}</dd>
                </div>
            </dl>

            <div>
                <h2 class="text-sm font-semibold text-clinic-text">Catatan Dokter</h2>
                <div class="mt-1.5 rounded-control border border-clinic-border bg-clinic-surface p-3 text-sm text-clinic-secondary whitespace-pre-line">
                    {{ $periksa->catatan ?: 'Tidak ada catatan.' }}
                </div>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-clinic-text">Obat yang Diberikan</h2>
                <ul class="mt-1.5 divide-y divide-clinic-border rounded-control border border-clinic-border overflow-hidden">
                    @forelse ($periksa->detailPeriksas as $detail)
                        <li class="flex items-center justify-between px-3 py-2 text-xs bg-clinic-surface">
                            <span class="font-medium text-clinic-text">{{ $detail->obat?->nama_obat }}</span>
                            <span class="font-semibold text-clinic-secondary tabular-nums">Rp{{ number_format($detail->obat?->harga ?? 0, 0, ',', '.') }}</span>
                        </li>
                    @empty
                        <li class="px-3 py-3 text-xs text-clinic-muted text-center bg-clinic-surface">Tidak ada obat yang dicatat.</li>
                    @endforelse
                </ul>
            </div>

            <div class="flex items-center justify-between border-t border-clinic-border pt-4 text-base font-bold text-clinic-text">
                <span>Total Biaya</span>
                <span class="tabular-nums">Rp{{ number_format($periksa->biaya_periksa, 0, ',', '.') }}</span>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
