<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\BackupDatabaseCommand;
use App\Console\Commands\RestoreDatabaseCommand;
use App\Console\Commands\SyncDatabaseCommand;
use App\Console\Commands\GenerateApiKeyCommand;
use App\Console\Commands\PullFromOnlineCommand;

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
        PullFromOnlineCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Pull updates from online server every minute
        $schedule->job(new PullFromOnlineJob)->everyMinute();
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