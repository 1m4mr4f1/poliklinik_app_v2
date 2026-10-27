<x-layouts.app :title="$title">
    <div class="mx-auto max-w-7xl space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-clinic-text">{{ $title }}</h1>
                <p class="mt-1 text-sm text-clinic-secondary">Kelola data dan pastikan informasi tetap terbaru.</p>
            </div>
            <x-ui.button href="{{ $createRoute }}" variant="primary">
                <i class="fas fa-plus mr-1"></i> Tambah Data
            </x-ui.button>
        </div>

        @if($items->isEmpty())
            <x-ui.empty-state message="Belum ada data." />
        @else
            <x-ui.table>
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                        <th class="w-1 whitespace-nowrap" style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            @foreach ($item['values'] as $value)
                                <td>{{ $value }}</td>
                            @endforeach
                            <td class="w-1 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <x-ui.button href="{{ $item['editUrl'] }}" variant="secondary" class="text-xs">
                                        <i class="fas fa-edit mr-1"></i> Ubah
                                    </x-ui.button>
                                    <form method="POST" action="{{ $item['deleteUrl'] }}"
                                          data-confirm="Hapus data ini secara permanen?"
                                          data-confirm-type="danger"
                                          data-confirm-title="Hapus Data"
                                          data-confirm-ok="Ya, hapus"
                                          onsubmit="return confirm('Hapus data ini?')">
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

        <div class="mt-4">{{ $items->links() }}</div>
    </div>
</x-layouts.app>
