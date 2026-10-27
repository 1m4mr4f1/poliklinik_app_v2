<x-layouts.app title="Detail Pemeriksaan">
    <div class="mx-auto max-w-3xl space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-clinic-text">Detail Pemeriksaan</h1>
                <p class="mt-1 text-sm text-clinic-secondary">
                    {{ $pendaftaran->jadwalPeriksa?->dokter?->poli?->nama_poli }} &middot; Dokter {{ $pendaftaran->jadwalPeriksa?->dokter?->nama }}
                </p>
            </div>
            <x-ui.button href="{{ route('pasien.riwayat.index') }}" variant="secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke riwayat
            </x-ui.button>
        </div>

        <x-ui.card class="space-y-4">
            <div>
                <h2 class="text-sm font-semibold text-clinic-text">Catatan Dokter</h2>
                <div class="mt-1.5 rounded-control border border-clinic-border bg-clinic-background p-3.5 text-sm text-clinic-secondary whitespace-pre-line">
                    {{ $periksa->catatan ?: 'Tidak ada catatan khusus dari dokter.' }}
                </div>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-clinic-text">Daftar Obat yang Diberikan</h2>
                <ul class="mt-1.5 divide-y divide-clinic-border rounded-control border border-clinic-border overflow-hidden">
                    @forelse ($periksa->detailPeriksas as $detail)
                        <li class="flex items-center justify-between px-3 py-2 text-xs bg-clinic-surface">
                            <span class="font-medium text-clinic-text">{{ $detail->obat?->nama_obat ?? 'Obat tidak tersedia' }}</span>
                            <span class="text-clinic-muted">{{ $detail->obat?->kemasan ?? '' }}</span>
                        </li>
                    @empty
                        <li class="px-3 py-3 text-xs text-clinic-muted text-center bg-clinic-surface">Tidak ada obat yang dicatat.</li>
                    @endforelse
                </ul>
            </div>

            <div class="flex items-center justify-between border-t border-clinic-border pt-4 text-base font-bold text-clinic-text">
                <span>Total Biaya Pemeriksaan</span>
                <span class="tabular-nums">Rp{{ number_format($periksa->biaya_periksa, 0, ',', '.') }}</span>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
