<x-layouts.app title="Dashboard Admin">
    <div class="mx-auto max-w-7xl space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-clinic-text">Dashboard Admin</h1>
                <p class="mt-1 text-sm text-clinic-secondary">Pemantauan antrean praktik hari {{ $hariIni }}.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.button href="{{ route('admin.export.dokter') }}" variant="secondary" data-no-page-loading>
                    <i class="fas fa-file-export mr-1"></i> Ekspor Dokter
                </x-ui.button>
                <x-ui.button href="{{ route('admin.export.pasien') }}" variant="secondary" data-no-page-loading>
                    <i class="fas fa-file-export mr-1"></i> Ekspor Pasien
                </x-ui.button>
                <x-ui.button href="{{ route('admin.export.obat') }}" variant="secondary" data-no-page-loading>
                    <i class="fas fa-file-export mr-1"></i> Ekspor Obat
                </x-ui.button>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <x-ui.card href="{{ route('admin.polis.index') }}" class="flex items-center gap-3 group">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-clinic-called-bg text-clinic-called transition-colors group-hover:bg-clinic-primary group-hover:text-white">
                    <i class="fas fa-hospital"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-clinic-secondary">Poli</p>
                    <span class="font-semibold text-clinic-primary group-hover:underline text-sm truncate block">Manajemen Poli</span>
                </div>
            </x-ui.card>
            <x-ui.card href="{{ route('admin.dokter.index') }}" class="flex items-center gap-3 group">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-clinic-called-bg text-clinic-called transition-colors group-hover:bg-clinic-primary group-hover:text-white">
                    <i class="fas fa-user-doctor"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-clinic-secondary">Dokter</p>
                    <span class="font-semibold text-clinic-primary group-hover:underline text-sm truncate block">Manajemen Dokter</span>
                </div>
            </x-ui.card>
            <x-ui.card href="{{ route('admin.pasien.index') }}" class="flex items-center gap-3 group">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-clinic-done-bg text-clinic-done transition-colors group-hover:bg-clinic-primary group-hover:text-white">
                    <i class="fas fa-user-injured"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-clinic-secondary">Pasien</p>
                    <span class="font-semibold text-clinic-primary group-hover:underline text-sm truncate block">Manajemen Pasien</span>
                </div>
            </x-ui.card>
            <x-ui.card href="{{ route('admin.obat.index') }}" class="flex items-center gap-3 group">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-clinic-waiting-bg text-clinic-waiting transition-colors group-hover:bg-clinic-primary group-hover:text-white">
                    <i class="fas fa-pills"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-clinic-secondary">Obat</p>
                    <span class="font-semibold text-clinic-primary group-hover:underline text-sm truncate block">Manajemen Obat</span>
                </div>
            </x-ui.card>
            <x-ui.card href="{{ route('admin.pembayaran.index') }}" class="flex items-center gap-3 group">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 transition-colors group-hover:bg-emerald-600 group-hover:text-white">
                    <i class="fas fa-money-check-dollar"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-clinic-secondary">Pembayaran</p>
                    <span class="font-semibold text-clinic-primary group-hover:underline text-sm truncate block">Verifikasi Bayar</span>
                </div>
            </x-ui.card>
        </div>

        <section class="space-y-4" aria-labelledby="antrian-title">
            <h2 id="antrian-title" class="text-base font-semibold text-clinic-text">Antrean Praktik Hari Ini</h2>

            @forelse ($jadwals as $jadwal)
                <x-ui.card data-queue-channel="antrian.{{ $jadwal->id }}.{{ today()->toDateString() }}">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded bg-clinic-background text-clinic-primary">
                                    <i class="fas fa-stethoscope text-xs"></i>
                                </span>
                                <h3 class="text-base font-semibold text-clinic-text">
                                    {{ $jadwal->dokter->poli?->nama_poli ?? 'Poli belum diatur' }} &mdash; {{ $jadwal->dokter->nama }}
                                </h3>
                            </div>
                            <p class="mt-1 text-xs text-clinic-secondary">
                                <i class="fas fa-clock mr-1 text-clinic-muted"></i>
                                {{ $jadwal->hari }} &middot; {{ substr($jadwal->jam_mulai, 0, 5) }}&ndash;{{ substr($jadwal->jam_selesai, 0, 5) }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-6 text-right">
                            <div>
                                <p class="text-xs text-clinic-secondary">Sedang dipanggil</p>
                                <p class="mt-1 text-xl font-bold tabular-nums text-clinic-called">
                                    #<span data-queue-number="{{ $jadwal->id }}">{{ $jadwal->antrianHariIni?->no_antrian_dipanggil ?? '—' }}</span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-clinic-secondary">Sudah diperiksa</p>
                                <p class="mt-1 text-base font-semibold tabular-nums text-clinic-done">
                                    <span data-examined-number="{{ $jadwal->id }}">{{ $jadwal->antrianHariIni?->no_antrian_diperiksa ?? 0 }}</span> pasien
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-clinic-border pt-4">
                        <p class="text-xs text-clinic-secondary">
                            <i class="fas fa-users mr-1 text-clinic-muted"></i>
                            <span class="font-semibold tabular-nums" data-pending-count="{{ $jadwal->id }}">{{ $jadwal->menunggu_diperiksa_count }}</span> pasien belum diperiksa.
                        </p>
                        <form method="POST" action="{{ route('admin.antrian.panggil-berikutnya', $jadwal) }}">
                            @csrf
                            <x-ui.button type="submit" variant="primary">
                                <i class="fas fa-bullhorn mr-1"></i> Panggil berikutnya
                            </x-ui.button>
                        </form>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.empty-state message="Tidak ada jadwal praktik untuk hari ini ({{ $hariIni }})." />
            @endforelse
        </section>
    </div>
</x-layouts.app>
