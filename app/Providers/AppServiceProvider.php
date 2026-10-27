<?php

namespace App\Providers;

use App\Models\Pembayaran;
use App\Models\Periksa;
use App\Observers\PembayaranObserver;
use App\Observers\PeriksaObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Pembayaran::observe(PembayaranObserver::class);
        Periksa::observe(PeriksaObserver::class);
    }
}
