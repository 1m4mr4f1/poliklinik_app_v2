<header class="topbar">
    <button id="sidebarToggle" type="button" onclick="toggleSidebar()"
        class="ui-button ui-button-secondary" aria-controls="appSidebar" aria-expanded="true"
        aria-label="Sembunyikan menu" title="Sembunyikan menu">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>

    <h1 class="min-w-0 flex-1 truncate text-xl font-semibold text-clinic-text">
        {{ $title ?? 'Poliklinik' }}
    </h1>

    <a href="{{ route('notifications.index') }}"
        class="relative inline-flex h-9 w-9 items-center justify-center rounded-md text-clinic-secondary transition-colors hover:bg-clinic-background hover:text-clinic-text"
        aria-label="Notifikasi">
        <i class="fas fa-bell" aria-hidden="true"></i>
        @php($unreadNotificationsCount = auth()->user()?->unreadNotifications()->count() ?? 0)
        @if($unreadNotificationsCount > 0)
            <span class="absolute right-0 top-0 inline-flex min-w-4 translate-x-1/4 -translate-y-1/4 items-center justify-center rounded-full bg-clinic-danger px-1 text-[10px] font-semibold leading-4 text-white">
                {{ $unreadNotificationsCount }}
            </span>
        @endif
    </a>

    <a href="{{ route('profile.password.edit') }}" class="flex max-w-48 items-center gap-2 text-clinic-text hover:text-clinic-primary">
        <span class="hidden truncate text-sm font-medium sm:block">
            {{ auth()->user()?->nama ?? 'Pengguna' }}
        </span>
        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-clinic-primary text-sm font-semibold text-white">
            {{ strtoupper(substr(auth()->user()?->nama ?? 'U', 0, 1)) }}
        </span>
    </a>
</header>
