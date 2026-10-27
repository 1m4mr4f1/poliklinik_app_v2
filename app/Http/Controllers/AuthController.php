<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak sesuai.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->dashboardRoute($request->user()));
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:255'],
            'no_ktp' => ['required', 'string', 'max:16', 'unique:users,no_ktp'],
            'no_hp' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create([
                'nama' => $validated['nama'],
                'alamat' => $validated['alamat'],
                'no_ktp' => $validated['no_ktp'],
                'no_hp' => $validated['no_hp'],
                'role' => 'pasien',
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            $user->update([
                'no_rm' => now()->format('Ym').'-'.str_pad((string) $user->id, 3, '0', STR_PAD_LEFT),
            ]);

            return $user;
        });

        return redirect()->route('login')->with('success', 'Pendaftaran akun berhasil. Silakan masuk.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function dashboardRoute(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'dokter' => route('dokter.dashboard'),
            'pasien' => route('pasien.dashboard'),
        };
    }
}
