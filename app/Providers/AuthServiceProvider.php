<?php

namespace App\Providers;

use App\Models\Biaya;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Policies\BiayaPolicy;
use App\Policies\PembayaranPolicy;
use App\Policies\SiswaPolicy;
use App\Policies\TagihanPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */    protected $policies = [
        Biaya::class => BiayaPolicy::class,
        Siswa::class => SiswaPolicy::class,
        Tagihan::class => TagihanPolicy::class,
        Pembayaran::class => PembayaranPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
