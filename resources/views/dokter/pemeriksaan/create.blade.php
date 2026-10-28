<x-layouts.app title="Pemeriksaan Pasien">
    <div class="mx-auto max-w-3xl space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-clinic-text">Pemeriksaan Pasien</h1>
                <p class="mt-1 text-sm text-clinic-secondary font-medium">
                    {{ $daftarPoli->pasien->nama }} &middot; No. RM {{ $daftarPoli->pasien->no_rm }}
                </p>
                <p class="text-xs text-clinic-muted">
                    {{ $daftarPoli->jadwalPeriksa->dokter->poli?->nama_poli }} &middot; Nomor antrian <strong class="text-clinic-primary font-semibold">#{{ $daftarPoli->no_antrian }}</strong>
                </p>
            </div>
            <x-ui.button href="{{ route('dokter.dashboard') }}" variant="secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </x-ui.button>
        </div>

        <x-ui.card>
            <form class="space-y-4" method="POST" action="{{ route('dokter.pemeriksaan.store', $daftarPoli) }}">
                @csrf

                @if($daftarPoli->keluhan)
                    <div class="rounded-control border border-clinic-border bg-clinic-background p-3 text-xs text-clinic-secondary">
                        <span class="font-semibold text-clinic-text">Keluhan pasien:</span>
                        <p class="mt-1">{{ $daftarPoli->keluhan }}</p>
                    </div>
                @endif

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-clinic-secondary" for="catatan">
                        Catatan pemeriksaan <span class="text-clinic-danger">*</span>
                    </label>
                    <textarea class="ui-input @error('catatan') border-clinic-danger @enderror"
                              id="catatan" name="catatan" rows="4" required
                              placeholder="Tuliskan diagnosis, anjuran dokter, dan tindakan...">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="text-xs text-clinic-danger" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-clinic-secondary" for="biaya_dokter">
                        Biaya Jasa Dokter <span class="text-clinic-danger">*</span>
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-semibold text-clinic-muted">Rp</span>
                        <input class="ui-input pl-9 @error('biaya_dokter') border-clinic-danger @enderror"
                               type="number" id="biaya_dokter" name="biaya_dokter" min="0" step="1000"
                               value="{{ old('biaya_dokter', 150000) }}" required
                               placeholder="150000">
                    </div>
                    <p class="text-[11px] text-clinic-muted">Tentukan tarif jasa periksa dokter untuk pasien ini (dapat diubah sesuai tindakan).</p>
                    @error('biaya_dokter')
                        <p class="text-xs text-clinic-danger" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-medium text-clinic-secondary">Obat yang diberikan (opsional)</label>
                    @error('obat_ids')
                        <p class="text-xs text-clinic-danger" role="alert">{{ $message }}</p>
                    @enderror

                    <div class="max-h-60 overflow-y-auto rounded-control border border-clinic-border p-3 space-y-2">
                        <div class="grid gap-2 sm:grid-cols-2">
                            @forelse ($obats as $obat)
                                <label class="flex items-center justify-between gap-2 rounded-control border border-clinic-border bg-clinic-surface p-2.5 text-xs hover:bg-clinic-background cursor-pointer">
                                    <span class="flex items-center gap-2">
                                        <input class="medicine-choice h-4 w-4 rounded accent-clinic-primary"
                                               type="checkbox" name="obat_ids[]" value="{{ $obat->id }}"
                                               data-price="{{ $obat->harga }}"
                                               @checked(in_array($obat->id, old('obat_ids', [])))>
                                        <span>
                                            <span class="font-medium text-clinic-text block">{{ $obat->nama_obat }}</span>
                                            <span class="text-clinic-muted block text-[11px]">{{ $obat->kemasan ?? 'tanpa kemasan' }}</span>
                                        </span>
                                    </span>
                                    <span class="font-semibold text-clinic-secondary tabular-nums">Rp{{ number_format($obat->harga, 0, ',', '.') }}</span>
                                </label>
                            @empty
                                <p class="text-xs text-clinic-muted col-span-2">Belum ada data obat.</p>
                            @endforelse
                        </div>
                    </div>
                    @error('obat_ids.*')
                        <p class="text-xs text-clinic-danger" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between border-t border-clinic-border pt-4">
                    <div>
                        <p class="text-xs text-clinic-secondary" id="total-breakdown-text">Total tagihan (jasa dokter + obat)</p>
                        <p class="text-lg font-bold tabular-nums text-clinic-text" id="estimated-total">Rp150.000</p>
                    </div>
                    <x-ui.button type="submit" variant="primary">
                        <i class="fas fa-check mr-1"></i> Selesaikan pemeriksaan
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

    @push('scripts')
    <script>
        const medicineChoices = document.querySelectorAll('.medicine-choice');
        const biayaDokterInput = document.getElementById('biaya_dokter');
        const totalElement = document.getElementById('estimated-total');
        const breakdownElement = document.getElementById('total-breakdown-text');

        const updateTotal = () => {
            const rawDoctorFee = parseInt(biayaDokterInput?.value, 10);
            const doctorFee = isNaN(rawDoctorFee) || rawDoctorFee < 0 ? 0 : rawDoctorFee;
            const medicineTotal = [...medicineChoices]
                .filter((choice) => choice.checked)
                .reduce((sum, choice) => sum + Number(choice.dataset.price), 0);
            const total = doctorFee + medicineTotal;

            if (totalElement) {
                totalElement.textContent = `Rp${new Intl.NumberFormat('id-ID').format(total)}`;
            }
            if (breakdownElement) {
                breakdownElement.textContent = `Total tagihan (Jasa: Rp${new Intl.NumberFormat('id-ID').format(doctorFee)} + Obat: Rp${new Intl.NumberFormat('id-ID').format(medicineTotal)})`;
            }
        };

        medicineChoices.forEach((choice) => choice.addEventListener('change', updateTotal));
        if (biayaDokterInput) {
            biayaDokterInput.addEventListener('input', updateTotal);
            biayaDokterInput.addEventListener('change', updateTotal);
        }
        updateTotal();
    </script>
    @endpush
</x-layouts.app>
