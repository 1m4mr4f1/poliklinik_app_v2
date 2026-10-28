<x-layouts.guest title="403 - Akses Ditolak">
    <div class="card bg-base-100 shadow-2xl rounded-2xl w-full max-w-[520px] border border-slate-200">
        <div class="card-body p-6 sm:p-8">
            <div class="flex items-center gap-3 border-b border-slate-200 pb-4 mb-4">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 text-lg">
                    <i class="fas fa-shield-halved" aria-hidden="true"></i>
                </span>
                <div>
                    <h1 class="text-xl font-bold text-slate-800">403 - Akses Ditolak</h1>
                    <p class="text-xs text-slate-500">Anda tidak memiliki hak akses ke halaman tersebut.</p>
                </div>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 text-xs text-amber-900 space-y-2 mb-5">
                <p class="font-semibold flex items-center gap-1.5 text-amber-950">
                    <i class="fas fa-circle-exclamation text-amber-600"></i>
                    Informasi Sesi Login Anda
                </p>
                @if(!empty($exception?->getMessage()) && $exception->getMessage() !== 'Unauthorized.')
                    <p>{{ $exception->getMessage() }}</p>
                @elseif(auth()->check())
                    <p>
                        Saat ini Anda sedang masuk sebagai <strong>{{ auth()->user()->nama ?? auth()->user()->name }}</strong> dengan peran/role <span class="badge badge-sm uppercase font-bold text-white bg-slate-700">{{ auth()->user()->role }}</span>. Halaman yang Anda buka membutuhkan hak akses untuk role yang berbeda.
                    </p>
                @else
                    <p>
                        Sesi login Anda tidak memiliki hak akses yang sesuai untuk membuka halaman ini.
                    </p>
                @endif
            </div>

            <div class="space-y-2">
                @if(auth()->check())
                    @php
                        $dashboardRoute = match(auth()->user()->role) {
                            'admin' => route('admin.dashboard'),
                            'dokter' => route('dokter.dashboard'),
                            default => route('pasien.dashboard'),
                        };
                    @endphp
                    <a href="{{ $dashboardRoute }}" class="ui-button ui-button-primary w-full">
                        <i class="fas fa-house mr-1"></i> Buka Dashboard Saya ({{ ucfirst(auth()->user()->role) }})
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="ui-button ui-button-danger w-full mt-2">
                            <i class="fas fa-right-from-bracket mr-1"></i> Keluar (Ganti Akun)
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="ui-button ui-button-primary w-full">
                        <i class="fas fa-right-to-bracket mr-1"></i> Kembali ke Halaman Login
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-layouts.guest>
