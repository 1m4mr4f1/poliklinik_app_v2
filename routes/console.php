<?php

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('poliklinik:create-admin', function () {
    $name = $this->ask('Nama admin');
    $email = $this->ask('Email admin');
    $password = $this->secret('Password admin');
    $confirmation = $this->secret('Ulangi password admin');

    $validator = Validator::make([
        'nama' => $name,
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $confirmation,
    ], [
        'nama' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'confirmed', Password::min(8)],
    ]);

    if ($validator->fails()) {
        $this->error($validator->errors()->first());

        return Command::FAILURE;
    }

    User::create([
        'nama' => $name,
        'role' => 'admin',
        'email' => $email,
        'password' => $password,
    ]);

    $this->info('Akun admin berhasil dibuat.');

    return Command::SUCCESS;
})->purpose('Buat akun admin pertama melalui prompt yang aman');
