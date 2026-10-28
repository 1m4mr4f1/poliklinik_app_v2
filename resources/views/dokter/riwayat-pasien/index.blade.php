<x-layouts.app title="Riwayat Pasien">
    <div class="mx-auto max-w-7xl space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-clinic-text">Riwayat Pasien</h1>
                <p class="mt-1 text-sm text-clinic-secondary">Riwayat pemeriksaan pasien pada jadwal praktik Anda.</p>
            </div>
            <x-ui.button href="{{ route('dokter.export.riwayat') }}" variant="secondary" data-no-page-loading>
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </x-ui.button>
        </div>

        @if($riwayatPasien->isEmpty())
            <x-ui.empty-state message="Belum ada riwayat pasien." />
        @else
            <x-ui.table>
                <thead>
                    <tr>
                        <th>Pasien</th>
                        <th>Tanggal Periksa</th>
                        <th>Keluhan</th>
                        <th class="numeric">Biaya</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riwayatPasien as $periksa)
                        <tr>
                            <td>
                                <span class="font-medium text-clinic-text">{{ $periksa->daftarPoli?->pasien?->nama }}</span>
                                <span class="block text-xs text-clinic-secondary">{{ $periksa->daftarPoli?->pasien?->no_rm }}</span>
                            </td>
                            <td>{{ $periksa->tgl_periksa->translatedFormat('d F Y, H:i') }}</td>
                            <td class="max-w-xs truncate">{{ $periksa->daftarPoli?->keluhan }}</td>
                            <td class="numeric font-semibold tabular-nums">Rp{{ number_format($periksa->biaya_periksa, 0, ',', '.') }}</td>
                            <td>
                                <x-ui.button href="{{ route('dokter.riwayat-pasien.show', $periksa) }}" variant="secondary" class="text-xs">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        @endif

        <div class="mt-4">{{ $riwayatPasien->links() }}</div>
    </div>
</x-layouts.app>
