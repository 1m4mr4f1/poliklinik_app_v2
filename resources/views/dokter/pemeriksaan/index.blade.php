<x-layouts.app title="Periksa Pasien">
    <div class="mx-auto max-w-7xl space-y-4">
        <div>
            <h1 class="text-xl font-bold text-clinic-text">Periksa Pasien</h1>
            <p class="mt-1 text-sm text-clinic-secondary">Daftar pasien hari ini pada jadwal praktik Anda.</p>
        </div>

        @if($daftarPasien->isEmpty())
            <x-ui.empty-state message="Belum ada pasien terdaftar pada jadwal Anda hari ini." />
        @else
            <x-ui.table>
                <thead>
                    <tr>
                        <th>No. Antrian</th>
                        <th>Pasien</th>
                        <th>Jadwal</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftarPasien as $pendaftaran)
                        <tr data-exam-registration="{{ $pendaftaran->id }}"
                            data-queue-channel="antrian.{{ $pendaftaran->id_jadwal }}.{{ today()->toDateString() }}"
                            data-exam-number="{{ $pendaftaran->no_antrian }}">
                            <td class="font-bold tabular-nums text-clinic-primary">#{{ $pendaftaran->no_antrian }}</td>
                            <td>
                                <span class="font-medium text-clinic-text">{{ $pendaftaran->pasien?->nama }}</span>
                                <span class="block text-xs text-clinic-secondary">{{ $pendaftaran->pasien?->no_rm }}</span>
                            </td>
                            <td>{{ $pendaftaran->jadwalPeriksa?->hari }}</td>
                            <td class="max-w-xs truncate">{{ $pendaftaran->keluhan }}</td>
                            <td>
                                <x-ui.status :status="$pendaftaran->periksa ? 'sudah diperiksa' : 'menunggu pemeriksaan'"
                                             data-exam-status="{{ $pendaftaran->id }}" />
                            </td>
                            <td>
                                @if ($pendaftaran->periksa)
                                    <span class="text-xs font-medium text-clinic-muted" data-exam-action>Selesai</span>
                                @else
                                    <x-ui.button href="{{ route('dokter.pemeriksaan.create', $pendaftaran) }}"
                                                 variant="primary" class="text-xs" data-exam-action>
                                        <i class="fas fa-stethoscope mr-1"></i> Periksa
                                    </x-ui.button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        @endif

        <div class="mt-4">{{ $daftarPasien->links() }}</div>
    </div>
</x-layouts.app>
