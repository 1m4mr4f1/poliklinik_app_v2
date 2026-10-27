<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DokterController as AdminDokterController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\PasienController as AdminPasienController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\Admin\PoliController;
use App\Http\Controllers\AntrianController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dokter\DashboardController as DokterDashboardController;
use App\Http\Controllers\Dokter\JadwalPeriksaController;
use App\Http\Controllers\Dokter\PemeriksaanController;
use App\Http\Controllers\Dokter\RiwayatPasienController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Pasien\DashboardController as PasienDashboardController;
use App\Http\Controllers\Pasien\PembayaranController;
use App\Http\Controllers\Pasien\PendaftaranController;
use App\Http\Controllers\Pasien\RiwayatPendaftaranController;
use App\Http\Controllers\PasswordController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/{notification}/baca', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::get('/profile/password', [PasswordController::class, 'edit'])->name('profile.password.edit');
    Route::put('/profile/password', [PasswordController::class, 'update'])->name('profile.password.update');
    Route::get('/pembayaran/{pembayaran}/bukti', [PembayaranController::class, 'proof'])->name('payments.proof');
});
Route::get('/dashboard', fn () => redirect()->route(match (auth()->user()?->role) {
    'admin' => 'admin.dashboard',
    'dokter' => 'dokter.dashboard',
    'pasien' => 'pasien.dashboard',
    default => 'login',
}))->middleware('auth')->name('dashboard');

Route::prefix('pasien')->name('pasien.')->middleware(['auth', 'role:pasien'])->group(function () {
    Route::get('/dashboard', PasienDashboardController::class)->name('dashboard');
    Route::get('/pendaftaran', [PendaftaranController::class, 'create'])->name('pendaftaran.create');
    Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');
    Route::get('/riwayat-pendaftaran', [RiwayatPendaftaranController::class, 'index'])->name('riwayat.index');
    Route::get('/riwayat-pendaftaran/{daftarPoli}', [RiwayatPendaftaranController::class, 'show'])->name('riwayat.show');
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran/{pembayaran}/bukti', [PembayaranController::class, 'upload'])->name('pembayaran.upload');
});

Route::prefix('dokter')->name('dokter.')->middleware(['auth', 'role:dokter'])->group(function () {
    Route::get('/dashboard', DokterDashboardController::class)->name('dashboard');
    Route::get('/pemeriksaan/{daftarPoli}', [PemeriksaanController::class, 'create'])->name('pemeriksaan.create');
    Route::post('/pemeriksaan/{daftarPoli}', [PemeriksaanController::class, 'store'])->name('pemeriksaan.store');
    Route::get('/periksa-pasien', [PemeriksaanController::class, 'index'])->name('periksa-pasien.index');
    Route::resource('jadwal-periksa', JadwalPeriksaController::class)->except('show');
    Route::get('/riwayat-pasien', [RiwayatPasienController::class, 'index'])->name('riwayat-pasien.index');
    Route::get('/riwayat-pasien/{periksa}', [RiwayatPasienController::class, 'show'])->name('riwayat-pasien.show');
    Route::post('/jadwal/{jadwalPeriksa}/panggil-berikutnya', [AntrianController::class, 'panggilBerikutnya'])
        ->name('antrian.panggil-berikutnya');
    Route::get('/export/jadwal-periksa', [ExportController::class, 'jadwal'])->name('export.jadwal');
    Route::get('/export/riwayat-pasien', [ExportController::class, 'riwayat'])->name('export.riwayat');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('polis', PoliController::class)->except('show');
    Route::resource('dokter', AdminDokterController::class)->except('show');
    Route::resource('pasien', AdminPasienController::class)->except('show');
    Route::resource('obat', ObatController::class)->except('show');
    Route::post('/jadwal/{jadwalPeriksa}/panggil-berikutnya', [AntrianController::class, 'panggilBerikutnya'])
        ->name('antrian.panggil-berikutnya');
    Route::get('/pembayaran', [AdminPembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran/{pembayaran}/verifikasi', [AdminPembayaranController::class, 'verify'])->name('pembayaran.verify');
    Route::get('/export/dokter', [ExportController::class, 'dokter'])->name('export.dokter');
    Route::get('/export/pasien', [ExportController::class, 'pasien'])->name('export.pasien');
    Route::get('/export/obat', [ExportController::class, 'obat'])->name('export.obat');
});
