<x-layouts.app title="Riwayat Pendaftaran">
    <div class="mx-auto max-w-7xl space-y-4">
        <div>
            <h1 class="text-xl font-bold text-clinic-text">Riwayat Pendaftaran Poli</h1>
            <p class="mt-1 text-sm text-clinic-secondary">Daftar pendaftaran poli Anda, diurutkan dari yang terbaru.</p>
        </div>

        @if($pendaftarans->isEmpty())
            <x-ui.empty-state message="Belum ada riwayat pendaftaran.">
                <x-slot:action>
                    <x-ui.button href="{{ route('pasien.pendaftaran.create') }}" variant="primary">
                        <i class="fas fa-plus mr-1"></i> Daftar Poli
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <x-ui.table>
                <thead>
                    <tr>
                        <th>Poli</th>
                        <th>Dokter</th>
                        <th>Tanggal Janji</th>
                        <th>No. Antrian</th>
                        <th>Status Pemeriksaan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendaftarans as $pendaftaran)
                        <tr data-exam-registration="{{ $pendaftaran->id }}"
                            data-queue-channel="antrian.{{ $pendaftaran->id_jadwal }}.{{ $pendaftaran->tanggal_janji->toDateString() }}"
                            data-exam-number="{{ $pendaftaran->no_antrian }}"
                            data-exam-detail-url="{{ route('pasien.riwayat.show', $pendaftaran) }}">
                            <td class="font-medium text-clinic-text">{{ $pendaftaran->jadwalPeriksa?->dokter?->poli?->nama_poli ?? '—' }}</td>
                            <td>{{ $pendaftaran->jadwalPeriksa?->dokter?->nama ?? '—' }}</td>
                            <td>{{ $pendaftaran->tanggal_janji->translatedFormat('d F Y') }}</td>
                            <td class="font-bold tabular-nums text-clinic-primary">#{{ $pendaftaran->no_antrian }}</td>
                            <td>
                                <x-ui.status :status="$pendaftaran->periksa ? 'sudah diperiksa' : 'menunggu pemeriksaan'"
                                             data-exam-status="{{ $pendaftaran->id }}" />
                            </td>
                            <td>
                                @if ($pendaftaran->periksa)
                                    <x-ui.button href="{{ route('pasien.riwayat.show', $pendaftaran) }}"
                                                 variant="secondary" class="text-xs" data-exam-action>
                                        <i class="fas fa-file-medical mr-1"></i> Detail
                                    </x-ui.button>
                                @else
                                    <span class="text-xs text-clinic-muted font-medium" data-exam-action>&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        @endif

        <div class="mt-4">{{ $pendaftarans->links() }}</div>
    </div>
</x-layouts.app>
