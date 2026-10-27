@php($role = auth()->user()?->role)

<aside class="flex min-h-screen w-full flex-col text-slate-200">
    <div class="border-b border-white/10 px-4 py-4">
        <a href="{{ $role === 'admin' ? route('admin.dashboard') : ($role === 'dokter' ? route('dokter.dashboard') : route('pasien.dashboard')) }}"
            class="flex items-center gap-3 text-white">
            <img src="{{ asset('images/logo-bengkod.png') }}" alt="" class="h-8 w-8 rounded-md object-cover">
            <span class="min-w-0">
                <span class="block text-sm font-semibold leading-5 text-white">Poliklinik</span>
                <span class="block text-xs leading-4 text-slate-300">{{ ucfirst($role ?? 'Pengguna') }}</span>
            </span>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Menu utama">
        @if($role === 'admin')
            <div class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                    <i class="fas fa-gauge-high w-4 text-center" aria-hidden="true"></i> Dashboard
                </a>
                <a href="{{ route('admin.polis.index') }}" class="sidebar-link" @if(request()->routeIs('admin.polis.*')) aria-current="page" @endif>
                    <i class="fas fa-hospital w-4 text-center" aria-hidden="true"></i> Data Poli
                </a>
                <a href="{{ route('admin.dokter.index') }}" class="sidebar-link" @if(request()->routeIs('admin.dokter.*')) aria-current="page" @endif>
                    <i class="fas fa-user-doctor w-4 text-center" aria-hidden="true"></i> Data Dokter
                </a>
                <a href="{{ route('admin.pasien.index') }}" class="sidebar-link" @if(request()->routeIs('admin.pasien.*')) aria-current="page" @endif>
                    <i class="fas fa-user-injured w-4 text-center" aria-hidden="true"></i> Data Pasien
                </a>
                <a href="{{ route('admin.obat.index') }}" class="sidebar-link" @if(request()->routeIs('admin.obat.*')) aria-current="page" @endif>
                    <i class="fas fa-pills w-4 text-center" aria-hidden="true"></i> Data Obat
                </a>
                <a href="{{ route('admin.pembayaran.index') }}" class="sidebar-link" @if(request()->routeIs('admin.pembayaran.*')) aria-current="page" @endif>
                    <i class="fas fa-money-check-dollar w-4 text-center" aria-hidden="true"></i> Verifikasi Pembayaran
                </a>
            </div>
        @elseif($role === 'dokter')
            <div class="space-y-1">
                <a href="{{ route('dokter.dashboard') }}" class="sidebar-link" @if(request()->routeIs('dokter.dashboard')) aria-current="page" @endif>
                    <i class="fas fa-gauge-high w-4 text-center" aria-hidden="true"></i> Dashboard
                </a>
                <a href="{{ route('dokter.periksa-pasien.index') }}" class="sidebar-link" @if(request()->routeIs('dokter.periksa-pasien.*') || request()->routeIs('dokter.pemeriksaan.*')) aria-current="page" @endif>
                    <i class="fas fa-stethoscope w-4 text-center" aria-hidden="true"></i> Periksa Pasien
                </a>
                <a href="{{ route('dokter.jadwal-periksa.index') }}" class="sidebar-link" @if(request()->routeIs('dokter.jadwal-periksa.*')) aria-current="page" @endif>
                    <i class="fas fa-calendar-days w-4 text-center" aria-hidden="true"></i> Jadwal Periksa
                </a>
                <a href="{{ route('dokter.riwayat-pasien.index') }}" class="sidebar-link" @if(request()->routeIs('dokter.riwayat-pasien.*')) aria-current="page" @endif>
                    <i class="fas fa-file-medical w-4 text-center" aria-hidden="true"></i> Riwayat Pasien
                </a>
            </div>
        @elseif($role === 'pasien')
            <div class="space-y-1">
                <a href="{{ route('pasien.dashboard') }}" class="sidebar-link" @if(request()->routeIs('pasien.dashboard')) aria-current="page" @endif>
                    <i class="fas fa-house-medical w-4 text-center" aria-hidden="true"></i> Dashboard
                </a>
                <a href="{{ route('pasien.pendaftaran.create') }}" class="sidebar-link" @if(request()->routeIs('pasien.pendaftaran.*')) aria-current="page" @endif>
                    <i class="fas fa-clipboard-list w-4 text-center" aria-hidden="true"></i> Daftar Poli
                </a>
                <a href="{{ route('pasien.riwayat.index') }}" class="sidebar-link" @if(request()->routeIs('pasien.riwayat.*')) aria-current="page" @endif>
                    <i class="fas fa-clock-rotate-left w-4 text-center" aria-hidden="true"></i> Riwayat Pendaftaran
                </a>
                <a href="{{ route('pasien.pembayaran.index') }}" class="sidebar-link" @if(request()->routeIs('pasien.pembayaran.*')) aria-current="page" @endif>
                    <i class="fas fa-receipt w-4 text-center" aria-hidden="true"></i> Pembayaran
                </a>
            </div>
        @endif

        <div class="mt-4 border-t border-white/10 pt-4">
            <a href="{{ route('profile.password.edit') }}" class="sidebar-link" @if(request()->routeIs('profile.*')) aria-current="page" @endif>
                <i class="fas fa-user-gear w-4 text-center" aria-hidden="true"></i> Profil & Password
            </a>
        </div>
    </nav>

    <div class="border-t border-white/10 p-3">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-action w-full cursor-pointer">
                <i class="fas fa-right-from-bracket" aria-hidden="true"></i> Keluar
            </button>
        </form>
    </div>
</aside>
