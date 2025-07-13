<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\BackupDatabaseCommand;
use App\Console\Commands\RestoreDatabaseCommand;
use App\Console\Commands\SyncDatabaseCommand;
use App\Console\Commands\GenerateApiKeyCommand;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        BackupDatabaseCommand::class,
        RestoreDatabaseCommand::class,
        SyncDatabaseCommand::class,
        GenerateApiKeyCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Bisa ditambahkan schedule backup otomatis di sini jika diperlukan
        // Contoh: backup setiap hari jam 00:00
        // $schedule->command('backup:run')->daily();

        // Auto sync database setiap 5 menit
        $schedule->command('sync:database both')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}