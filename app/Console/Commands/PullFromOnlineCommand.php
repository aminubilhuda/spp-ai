<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\PullFromOnlineJob;

class PullFromOnlineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:pull-from-online';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a job to pull latest data updates from the online server.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        PullFromOnlineJob::dispatch();
        $this->info('The job to pull data from the online server has been dispatched successfully.');
        $this->comment('Please make sure your queue worker is running to process the job.');
        return 0;
    }
}
