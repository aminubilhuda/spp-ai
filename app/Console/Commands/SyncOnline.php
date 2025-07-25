<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Jobs\SyncToOnlineJob;

class SyncOnline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:online';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all unsynced data to online server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting data synchronization...');

        // Sync Siswa
        $this->info('Syncing Siswa data...');
        $unsyncedSiswa = Siswa::where('synced', false)->get();
        foreach ($unsyncedSiswa as $siswa) {
            SyncToOnlineJob::dispatch('created', $siswa->toArray());
            $this->info("Dispatched sync job for Siswa ID: {$siswa->id}");
        }

        // Sync Pembayaran
        $this->info('Syncing Pembayaran data...');
        $unsyncedPembayaran = Pembayaran::where('synced', false)->get();
        foreach ($unsyncedPembayaran as $pembayaran) {
            SyncToOnlineJob::dispatch('created', $pembayaran->toArray());
            $this->info("Dispatched sync job for Pembayaran ID: {$pembayaran->id}");
        }

        $this->info('Data synchronization complete.');
    }
}