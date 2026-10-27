<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('profile.password');
    }

    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
