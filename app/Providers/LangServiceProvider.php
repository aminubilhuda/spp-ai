<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class LangServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Konfigurasi bahasa dari laravel-lang/lang
        // Laravel 11 sudah memiliki cara baru untuk mengelola file bahasa
    }
}
