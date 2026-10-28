<x-layouts.app title="Dashboard Pasien">
    <div class="mx-auto max-w-7xl space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-clinic-text">Dashboard Pasien</h1>
                <p class="mt-1 text-sm text-clinic-secondary">
                    Selamat datang, <strong class="text-clinic-text">{{ auth()->user()->nama }}</strong> &middot; No. RM: <span class="font-semibold text-clinic-primary">{{ auth()->user()->no_rm }}</span>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.button href="{{ route('pasien.riwayat.index') }}" variant="secondary">
                    <i class="fas fa-clock-rotate-left mr-1"></i> Riwayat
                </x-ui.button>
                <x-ui.button href="{{ route('pasien.pembayaran.index') }}" variant="secondary">
                    <i class="fas fa-receipt mr-1"></i> Pembayaran
                </x-ui.button>
                <x-ui.button href="{{ route('pasien.pendaftaran.create') }}" variant="primary">
                    <i class="fas fa-plus mr-1"></i> Daftar Poli
                </x-ui.button>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <x-ui.card href="{{ route('pasien.pendaftaran.create') }}" class="flex items-center gap-3 group">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-clinic-called-bg text-clinic-called transition-colors group-hover:bg-clinic-primary group-hover:text-white">
                    <i class="fas fa-clipboard-list"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-clinic-secondary">Layanan Poli</p>
                    <span class="font-semibold text-clinic-primary group-hover:underline text-sm truncate block">Daftar Poli</span>
                </div>
            </x-ui.card>
            <x-ui.card href="{{ route('pasien.riwayat.index') }}" class="flex items-center gap-3 group">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-clinic-done-bg text-clinic-done transition-colors group-hover:bg-clinic-primary group-hover:text-white">
                    <i class="fas fa-clock-rotate-left"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-clinic-secondary">Catatan Medis</p>
                    <span class="font-semibold text-clinic-primary group-hover:underline text-sm truncate block">Riwayat Pemeriksaan</span>
                </div>
            </x-ui.card>
            <x-ui.card href="{{ route('pasien.pembayaran.index') }}" class="flex items-center gap-3 group">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 transition-colors group-hover:bg-emerald-600 group-hover:text-white">
                    <i class="fas fa-receipt"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-clinic-secondary">Tagihan Biaya</p>
                    <span class="font-semibold text-clinic-primary group-hover:underline text-sm truncate block">Status Pembayaran</span>
                </div>
            </x-ui.card>
        </div>

        @if ($pendaftaranAktif)
            @php
                $jadwalAktif = $pendaftaranAktif->jadwalPeriksa;
                $nomorDipanggilAktif = \App\Models\AntrianHarian::query()
                    ->where('id_jadwal', $pendaftaranAktif->id_jadwal)
                    ->whereDate('tanggal', $pendaftaranAktif->tanggal_janji)
                    ->value('no_antrian_dipanggil');
            @endphp
            <div
                class="rounded-xl bg-clinic-sidebar p-5 text-white shadow-lg"
                style="background-color: var(--color-clinic-sidebar); color: #ffffff;"
                data-queue-channel="antrian.{{ $jadwalAktif->id }}.{{ $pendaftaranAktif->tanggal_janji->toDateString() }}"
            >
                <div class="flex items-center justify-between border-b border-white/30 pb-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-100">
                        <i class="fas fa-ticket mr-1"></i> Antrian aktif Anda Hari Ini
                    </span>
                    <span class="rounded bg-white/20 px-2 py-0.5 text-xs font-medium text-white">
                        {{ $pendaftaranAktif->tanggal_janji->translatedFormat('d F Y') }}
                    </span>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="text-xs text-blue-100">Poli Tujuan</p>
                        <p class="mt-1 text-base font-semibold text-white">{{ $jadwalAktif->dokter->poli?->nama_poli ?? 'Belum ditentukan' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-blue-100">Dokter</p>
                        <p class="mt-1 text-base font-semibold text-white">{{ $jadwalAktif->dokter->nama }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-blue-100">Jam Praktik</p>
                        <p class="mt-1 text-sm font-medium text-white">{{ $jadwalAktif->hari }}, {{ substr($jadwalAktif->jam_mulai, 0, 5) }}&ndash;{{ substr($jadwalAktif->jam_selesai, 0, 5) }}</p>
                    </div>
                    <div class="rounded-control bg-white/20 p-3 text-center sm:text-left">
                        <p class="text-xs text-blue-100">Nomor antrian Anda</p>
                        <p class="mt-1 text-3xl font-extrabold tabular-nums text-white">#{{ $pendaftaranAktif->no_antrian }}</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center justify-between gap-4 border-t border-white/30 pt-4 text-xs">
                    <div>
                        <span class="text-blue-100">Sedang dipanggil:</span>
                        <strong class="ml-1 text-sm text-white">
                            #<span data-queue-number="{{ $jadwalAktif->id }}">{{ $nomorDipanggilAktif ?? 'Belum ada' }}</span>
                        </strong>
                    </div>
                    <div>
                        <span class="text-blue-100">Sudah diperiksa:</span>
                        <strong class="ml-1 text-sm text-white">
                            <span data-examined-number="{{ $jadwalAktif->id }}">{{ $nomorDiperiksaAktif ?? 0 }}</span> pasien
                        </strong>
                    </div>
                </div>
            </div>
        @else
            <x-ui.empty-state message="Anda belum memiliki antrian aktif saat ini.">
                <x-slot:action>
                    <x-ui.button href="{{ route('pasien.pendaftaran.create') }}" variant="primary">
                        <i class="fas fa-clipboard-list mr-1"></i> Daftar Poli Sekarang
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @endif

        <section class="space-y-3" aria-labelledby="jadwal-title">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="jadwal-title" class="text-base font-semibold text-clinic-text">Jadwal Poliklinik Hari Ini</h2>
                <p class="text-xs text-clinic-secondary">{{ now()->translatedFormat('l, d F Y') }} &middot; antrean diperbarui otomatis.</p>
            </div>

            @if($jadwals->isEmpty())
                <x-ui.empty-state message="Belum ada jadwal praktik untuk hari ini." />
            @else
                <x-ui.table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Poli</th>
                            <th>Dokter</th>
                            <th>Hari</th>
                            <th>Jam Periksa</th>
                            <th>Sedang Dipanggil</th>
                            <th>Sudah Diperiksa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwals as $jadwal)
                            <tr data-queue-channel="antrian.{{ $jadwal->id }}.{{ today()->toDateString() }}">
                                <td class="tabular-nums">{{ $loop->iteration }}</td>
                                <td class="font-medium text-clinic-text">{{ $jadwal->dokter->poli?->nama_poli ?? 'Belum ditentukan' }}</td>
                                <td>{{ $jadwal->dokter->nama }}</td>
                                <td>{{ $jadwal->hari }}</td>
                                <td class="tabular-nums">{{ substr($jadwal->jam_mulai, 0, 5) }}&ndash;{{ substr($jadwal->jam_selesai, 0, 5) }}</td>
                                <td class="font-semibold text-clinic-called">
                                    #<span data-queue-number="{{ $jadwal->id }}">{{ $jadwal->antrianHariIni?->no_antrian_dipanggil ?? 'Belum ada' }}</span>
                                </td>
                                <td class="font-semibold text-clinic-done">
                                    <span data-examined-number="{{ $jadwal->id }}">{{ $jadwal->antrianHariIni?->no_antrian_diperiksa ?? 0 }}</span> pasien
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </section>
    </div>
</x-layouts.app>
