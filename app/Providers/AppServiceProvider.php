<?php

namespace App\Providers;

use App\Models\Pembayaran;
use App\Observers\PembayaranObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        Paginator::useBootstrap();
        
        // Register observers
        Pembayaran::observe(PembayaranObserver::class);
    }
}