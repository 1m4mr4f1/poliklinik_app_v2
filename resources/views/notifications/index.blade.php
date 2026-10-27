<x-layouts.app title="Notifikasi">
    <div class="mx-auto max-w-4xl space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-clinic-text">Notifikasi</h1>
                <p class="mt-1 text-sm text-clinic-secondary">{{ $unreadCount }} notifikasi belum dibaca.</p>
            </div>
        </div>

        <section class="space-y-3">
            @forelse ($notifications as $notification)
                <x-ui.card class="{{ $notification->read_at ? 'bg-clinic-surface' : 'bg-clinic-called-bg/30 border-clinic-called/30' }}">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $notification->read_at ? 'bg-clinic-background text-clinic-muted' : 'bg-clinic-primary text-white' }}">
                                <i class="fas fa-bell text-xs"></i>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-clinic-text">{{ $notification->data['message'] ?? 'Anda memiliki notifikasi baru.' }}</p>
                                <time class="mt-1 block text-xs text-clinic-secondary" datetime="{{ $notification->created_at->toIso8601String() }}">
                                    {{ $notification->created_at->translatedFormat('d F Y, H:i') }}
                                </time>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if (! empty($notification->data['url']))
                                <x-ui.button href="{{ $notification->data['url'] }}" variant="secondary" class="text-xs">
                                    Lihat
                                </x-ui.button>
                            @endif
                            @if ($notification->read_at === null)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="primary" class="text-xs">
                                        Tandai sudah dibaca
                                    </x-ui.button>
                                </form>
                            @else
                                <span class="text-xs font-medium text-clinic-muted">Sudah dibaca</span>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.empty-state message="Belum ada notifikasi." />
            @endforelse
        </section>

        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>
</x-layouts.app>
