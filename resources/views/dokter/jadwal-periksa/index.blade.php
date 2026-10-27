<x-layouts.app title="Jadwal Periksa">
    <div class="mx-auto max-w-7xl space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-clinic-text">Jadwal Periksa</h1>
                <p class="mt-1 text-sm text-clinic-secondary">Jadwal praktik yang dikelola untuk akun dokter ini.</p>
            </div>
            <x-ui.button href="{{ route('dokter.jadwal-periksa.create') }}" variant="primary">
                <i class="fas fa-plus mr-1"></i> Tambah Jadwal
            </x-ui.button>
        </div>

        @if($jadwals->isEmpty())
            <x-ui.empty-state message="Belum ada jadwal praktik." />
        @else
            <x-ui.table>
                <thead>
                    <tr>
                        <th>Hari</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jadwals as $jadwal)
                        <tr>
                            <td class="font-medium text-clinic-text">{{ $jadwal->hari }}</td>
                            <td class="tabular-nums">{{ substr($jadwal->jam_mulai, 0, 5) }}</td>
                            <td class="tabular-nums">{{ substr($jadwal->jam_selesai, 0, 5) }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <x-ui.button href="{{ route('dokter.jadwal-periksa.edit', $jadwal) }}" variant="secondary" class="text-xs">
                                        <i class="fas fa-edit mr-1"></i> Ubah
                                    </x-ui.button>
                                    <form method="POST" action="{{ route('dokter.jadwal-periksa.destroy', $jadwal) }}"
                                          data-confirm="Hapus jadwal praktik ini?"
                                          data-confirm-type="danger"
                                          data-confirm-title="Hapus Jadwal"
                                          data-confirm-ok="Ya, hapus"
                                          onsubmit="return confirm('Hapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" variant="danger" class="text-xs">
                                            <i class="fas fa-trash-alt mr-1"></i> Hapus
                                        </x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        @endif

        <div class="mt-4">{{ $jadwals->links() }}</div>
    </div>
</x-layouts.app>
