<x-layouts.guest title="Masuk">

    <div class="card bg-base-100 shadow-2xl rounded-2xl w-full max-w-[420px]">
        <div class="card-body my-2 p-[40px]">

            {{-- Logo & Title --}}
            <div class="text-center mb-8">
                <img src="{{ asset('images/logo-bengkod.png') }}"
                    class="w-[60px] h-[60px] rounded-[16px] object-cover mx-auto mb-[14px] block" alt="Logo">

                <h1 class="text-[1.5rem] font-extrabold text-[#1e2d6b] m-0 mb-[6px]">
                    Poliklinik
                </h1>

                <p class="text-[0.83rem] text-slate-400 m-0">
                    Masuk ke akun Anda
                </p>
            </div>

            {{-- Success Alert --}}
            @if (session('success'))
                <div class="alert alert-success mb-5 rounded-xl text-[0.83rem]">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Error Alert --}}
            @if ($errors->any())
                <div class="alert alert-error mb-5 rounded-xl text-[0.83rem]">
                    <i class="fas fa-circle-xmark"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                {{-- Email --}}
                <div class="form-control mb-4">
                    <label class="label pb-1" for="email">
                        <span class="text-[0.82rem] font-semibold text-gray-700">
                            Email
                        </span>
                    </label>

                    <label class="input input-bordered flex items-center gap-3 rounded-[10px] border-slate-200 bg-slate-50">
                        <i class="fas fa-envelope text-slate-400 text-[0.82rem]"></i>

                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email..."
                            class="grow bg-transparent text-slate-800 text-[0.88rem]" required autofocus autocomplete="email">
                    </label>
                </div>

                {{-- Password --}}
                <div class="form-control mb-6">
                    <label class="label pb-1" for="password_login">
                        <span class="text-[0.82rem] font-semibold text-gray-700">
                            Password
                        </span>
                    </label>

                    <label class="input input-bordered flex items-center gap-3 rounded-[10px] border-slate-200 bg-slate-50">
                        <i class="fas fa-lock text-slate-400 text-[0.82rem]"></i>

                        <input type="password" name="password" id="password_login" placeholder="Masukkan password..."
                            class="grow bg-transparent text-slate-800 text-[0.88rem]" required autocomplete="current-password">

                        <i class="fas fa-eye text-slate-400 text-[0.82rem] cursor-pointer" id="toggle_login"
                            onclick="togglePassword('password_login', 'toggle_login')"></i>
                    </label>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn-primary-gradient mt-6">
                    <i class="fas fa-right-to-bracket"></i>
                    Masuk
                </button>
            </form>

            {{-- Register --}}
            <p class="text-center mt-5 text-[0.83rem] text-slate-400">
                Belum punya akun pasien?
                <a href="{{ route('register') }}" class="text-[#2d4499] font-bold no-underline hover:underline">
                    Daftar
                </a>
            </p>

        </div>
    </div>

    @push('scripts')
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
    @endpush

</x-layouts.guest>
