<x-layouts.app title="Profil & Password">
    <div class="mx-auto max-w-2xl space-y-4">
        <div>
            <h1 class="text-xl font-bold text-clinic-text">Profil & Password</h1>
            <p class="mt-1 text-sm text-clinic-secondary">Perbarui password akun {{ auth()->user()->nama }} ({{ ucfirst(auth()->user()->role) }}).</p>
        </div>

        <x-ui.card>
            <form class="space-y-4" method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-clinic-secondary" for="current_password">Password saat ini</label>
                    <input class="ui-input @error('current_password') border-clinic-danger @enderror"
                           id="current_password" name="current_password" type="password"
                           autocomplete="current-password" required>
                    @error('current_password')
                        <p class="text-xs text-clinic-danger" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-clinic-secondary" for="password">Password baru</label>
                    <input class="ui-input @error('password') border-clinic-danger @enderror"
                           id="password" name="password" type="password"
                           autocomplete="new-password" required>
                    <p class="text-xs text-clinic-muted">Minimal 8 karakter dan harus berbeda dari password lama.</p>
                    @error('password')
                        <p class="text-xs text-clinic-danger" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-clinic-secondary" for="password_confirmation">Konfirmasi password baru</label>
                    <input class="ui-input @error('password_confirmation') border-clinic-danger @enderror"
                           id="password_confirmation" name="password_confirmation" type="password"
                           autocomplete="new-password" required>
                    @error('password_confirmation')
                        <p class="text-xs text-clinic-danger" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <x-ui.button type="submit">
                        <i class="fas fa-key mr-1"></i> Simpan Password
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
