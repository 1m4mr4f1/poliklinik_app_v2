<x-layouts.app title="Verifikasi Pembayaran">
    <div class="mx-auto max-w-7xl space-y-4">
        <div>
            <h1 class="text-xl font-bold text-clinic-text">Verifikasi Pembayaran</h1>
            <p class="mt-1 text-sm text-clinic-secondary">Periksa bukti pembayaran pasien sebelum mengonfirmasi status lunas.</p>
        </div>

        <section class="space-y-4">
            @forelse ($pembayarans as $pembayaran)
                @php($periksa = $pembayaran->periksa)
                @php($pendaftaran = $periksa?->daftarPoli)
                <x-ui.card>
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded bg-clinic-background text-clinic-primary">
                                    <i class="fas fa-receipt text-xs"></i>
                                </span>
                                <h2 class="text-base font-semibold text-clinic-text">
                                    {{ $pendaftaran?->pasien?->nama ?? 'Pasien' }}
                                    <span class="text-xs font-normal text-clinic-secondary">&middot; No. RM {{ $pendaftaran?->pasien?->no_rm ?? '—' }}</span>
                                </h2>
                            </div>
                            <p class="mt-1 text-xs text-clinic-secondary">
                                {{ $pendaftaran?->jadwalPeriksa?->dokter?->poli?->nama_poli ?? 'Poli' }} &middot; Dokter {{ $pendaftaran?->jadwalPeriksa?->dokter?->nama ?? '—' }}
                            </p>
                            <p class="mt-2 text-sm font-semibold tabular-nums text-clinic-text">
                                Tagihan: Rp{{ number_format($periksa?->biaya_periksa ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div>
                            <x-ui.status status="menunggu_verifikasi" />
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-clinic-border pt-4">
                        <x-ui.button href="{{ route('payments.proof', $pembayaran) }}" target="_blank" rel="noopener" variant="secondary" data-no-page-loading>
                            <i class="fas fa-image mr-1"></i> Lihat bukti pembayaran
                        </x-ui.button>
                        <form method="POST" action="{{ route('admin.pembayaran.verify', $pembayaran) }}"
                              data-confirm="Konfirmasi pembayaran ini sebagai lunas?"
                              data-confirm-type="warning"
                              data-confirm-title="Konfirmasi Pembayaran"
                              data-confirm-ok="Ya, konfirmasi lunas">
                            @csrf
                            <x-ui.button type="submit" variant="primary">
                                <i class="fas fa-check mr-1"></i> Konfirmasi lunas
                            </x-ui.button>
                        </form>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.empty-state message="Tidak ada pembayaran yang menunggu verifikasi." />
            @endforelse
        </section>

        <div class="mt-4">{{ $pembayarans->links() }}</div>
    </div>
</x-layouts.app>
