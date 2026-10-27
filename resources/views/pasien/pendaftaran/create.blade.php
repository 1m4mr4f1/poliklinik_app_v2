<x-layouts.app title="Pendaftaran Poli">
    <div class="mx-auto max-w-2xl space-y-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-clinic-text">Pendaftaran Poli</h1>
                <p class="mt-1 text-sm text-clinic-secondary">Pilih tanggal dan jadwal praktik dokter yang sesuai.</p>
            </div>
            <x-ui.button href="{{ route('pasien.dashboard') }}" variant="secondary">
                <i class="fas fa-arrow-left mr-1"></i> Dashboard
            </x-ui.button>
        </div>

        <x-ui.card>
            <form class="space-y-4" method="POST" action="{{ route('pasien.pendaftaran.store') }}">
                @csrf

                <div class="rounded-control border border-clinic-border bg-clinic-background p-3">
                    <p class="text-xs text-clinic-secondary">Informasi Pasien</p>
                    <p class="font-semibold text-clinic-text text-sm mt-0.5">
                        {{ auth()->user()->nama }} &middot; No. RM: <span class="text-clinic-primary">{{ auth()->user()->no_rm }}</span>
                    </p>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-clinic-secondary" for="tanggal_janji">
                        Tanggal Janji <span class="text-clinic-danger">*</span>
                    </label>
                    <input class="ui-input @error('tanggal_janji') border-clinic-danger @enderror"
                           type="date" id="tanggal_janji" name="tanggal_janji"
                           min="{{ today()->toDateString() }}"
                           value="{{ old('tanggal_janji') }}"
                           data-appointment-date required>
                    @error('tanggal_janji')
                        <p class="text-xs text-clinic-danger" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-clinic-secondary" for="id_jadwal">
                        Poli, Dokter, dan Jadwal <span class="text-clinic-danger">*</span>
                    </label>
                    <select class="ui-input @error('id_jadwal') border-clinic-danger @enderror"
                            id="id_jadwal" name="id_jadwal"
                            data-appointment-schedule required>
                        <option value="">Pilih jadwal yang sesuai dengan tanggal di atas</option>
                        @foreach ($jadwals as $jadwal)
                            <option
                                value="{{ $jadwal->id }}"
                                data-weekday="{{ ['Minggu' => 0, 'Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6][$jadwal->hari] }}"
                                @selected((string) old('id_jadwal') === (string) $jadwal->id)
                            >
                                {{ $jadwal->dokter->poli->nama_poli }} &mdash; {{ $jadwal->dokter->nama }} ({{ $jadwal->hari }}, {{ substr($jadwal->jam_mulai, 0, 5) }}&ndash;{{ substr($jadwal->jam_selesai, 0, 5) }})
                            </option>
                        @endforeach
                    </select>
                    @if ($jadwals->isEmpty())
                        <p class="text-xs text-clinic-waiting mt-1">Belum ada jadwal dokter yang tersedia.</p>
                    @endif
                    @error('id_jadwal')
                        <p class="text-xs text-clinic-danger" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-clinic-secondary" for="keluhan">
                        Keluhan yang Dirasakan <span class="text-clinic-danger">*</span>
                    </label>
                    <textarea class="ui-input @error('keluhan') border-clinic-danger @enderror"
                              id="keluhan" name="keluhan" rows="4" required
                              placeholder="Deskripsikan gejala atau keluhan kesehatan Anda...">{{ old('keluhan') }}</textarea>
                    @error('keluhan')
                        <p class="text-xs text-clinic-danger" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <x-ui.button type="submit" variant="primary" class="w-full">
                        <i class="fas fa-check mr-1"></i> Daftar Antrean
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
