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
                        @php
                            $antrianHariIni = $pendaftaran->jadwalPeriksa?->antrianHariIni;
                            $sedangDipanggil = ! $pendaftaran->periksa
                                && $antrianHariIni?->no_antrian_dipanggil !== null
                                && (int) $antrianHariIni->no_antrian_dipanggil === (int) $pendaftaran->no_antrian;
                        @endphp
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
                                <x-ui.status :status="$pendaftaran->periksa ? 'sudah diperiksa' : ($sedangDipanggil ? 'dipanggil' : 'menunggu pemeriksaan')"
                                             data-exam-status="{{ $pendaftaran->id }}" />
                            </td>
                            <td>
                                @if ($pendaftaran->periksa)
                                    <span class="text-xs font-medium text-clinic-muted" data-exam-action>Selesai</span>
                                @else
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('dokter.pemeriksaan.create', $pendaftaran) }}"
                                           class="{{ $sedangDipanggil ? 'inline-flex' : 'hidden' }} ui-button ui-button-primary text-xs"
                                           data-exam-link="{{ $pendaftaran->id_jadwal }}"
                                           data-exam-number="{{ $pendaftaran->no_antrian }}"
                                           data-exam-action>
                                            <i class="fas fa-stethoscope mr-1"></i> Periksa
                                        </a>
                                        @if (! $sedangDipanggil && $pendaftaran->jadwalPeriksa)
                                            <form method="POST" action="{{ route('dokter.antrian.panggil-berikutnya', $pendaftaran->jadwalPeriksa) }}">
                                                @csrf
                                                <x-ui.button type="submit" variant="secondary" class="text-xs">
                                                    <i class="fas fa-bullhorn mr-1"></i> Panggil Antrian
                                                </x-ui.button>
                                            </form>
                                        @endif
                                    </div>
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
