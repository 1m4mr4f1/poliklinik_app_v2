<x-layouts.app title="Pembayaran">
    <div class="mx-auto max-w-7xl space-y-4">
        <div>
            <h1 class="text-xl font-bold text-clinic-text">Pembayaran</h1>
            <p class="mt-1 text-sm text-clinic-secondary">Tagihan pemeriksaan dan status verifikasi pembayaran Anda.</p>
        </div>

        <section class="space-y-4">
            @forelse ($periksas as $periksa)
                @php($pembayaran = $periksa->pembayaran)
                <x-ui.card>
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded bg-clinic-background text-clinic-primary">
                                    <i class="fas fa-receipt text-xs"></i>
                                </span>
                                <h2 class="text-base font-semibold text-clinic-text">
                                    {{ $periksa->daftarPoli?->jadwalPeriksa?->dokter?->poli?->nama_poli ?? 'Poli' }}
                                </h2>
                            </div>
                            <p class="mt-1 text-xs text-clinic-secondary">
                                Dokter {{ $periksa->daftarPoli?->jadwalPeriksa?->dokter?->nama ?? '—' }} &middot; {{ $periksa->tgl_periksa->translatedFormat('d F Y, H:i') }}
                            </p>
                            <p class="mt-2 text-base font-bold tabular-nums text-clinic-text">
                                Total: Rp{{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                            </p>
                        </div>
                        <div>
                            <x-ui.status :status="$pembayaran->status" />
                        </div>
                    </div>

                    @if ($pembayaran->bukti_path)
                        <div class="mt-3">
                            <x-ui.button href="{{ route('payments.proof', $pembayaran) }}" target="_blank" rel="noopener" variant="secondary" class="text-xs" data-no-page-loading>
                                <i class="fas fa-image mr-1"></i> Lihat bukti pembayaran yang diunggah
                            </x-ui.button>
                        </div>
                    @endif

                    @if ($pembayaran->status === 'belum_dibayar')
                        <form class="mt-4 flex flex-wrap items-end gap-3 border-t border-clinic-border pt-4" method="POST" enctype="multipart/form-data" action="{{ route('pasien.pembayaran.upload', $pembayaran) }}">
                            @csrf
                            <div class="min-w-60 flex-1 space-y-1">
                                <label class="block text-xs font-medium text-clinic-secondary" for="bukti-{{ $pembayaran->id }}">
                                    Unggah foto bukti transfer / pembayaran (JPG, PNG, atau WEBP; maks. 5 MB) <span class="text-clinic-danger">*</span>
                                </label>
                                <input class="ui-input text-xs file:mr-3 file:rounded file:border-0 file:bg-clinic-primary file:px-3 file:py-1 file:text-xs file:font-semibold file:text-white"
                                       id="bukti-{{ $pembayaran->id }}" name="bukti" type="file"
                                       accept="image/jpeg,image/png,image/webp" required>
                                @error('bukti')
                                    <p class="text-xs text-clinic-danger" role="alert">{{ $message }}</p>
                                @enderror
                            </div>
                            <x-ui.button type="submit" variant="primary">
                                <i class="fas fa-upload mr-1"></i> Unggah Bukti
                            </x-ui.button>
                        </form>
                    @endif
                </x-ui.card>
            @empty
                <x-ui.empty-state message="Belum ada tagihan pemeriksaan." />
            @endforelse
        </section>

        <div class="mt-4">{{ $periksas->links() }}</div>
    </div>
</x-layouts.app>
