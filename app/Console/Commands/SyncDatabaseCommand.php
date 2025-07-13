<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseSyncService;

class SyncDatabaseCommand extends Command
{
    protected $signature = 'sync:database {direction=both : Direction sync (to-online, from-online, both)}';
    protected $description = 'Sinkronisasi database antara local dan online';

    public function handle(DatabaseSyncService $syncService)
    {
        $direction = $this->argument('direction');

        $this->info('Memulai sinkronisasi database...');

        switch ($direction) {
            case 'to-online':
                $this->info('Sinkronisasi ke online...');
                $result = $syncService->syncToOnline();
                if ($result) {
                    $this->info('✅ Sinkronisasi ke online berhasil');
                } else {
                    $this->error('❌ Sinkronisasi ke online gagal');
                }
                break;

            case 'from-online':
                $this->info('Sinkronisasi dari online...');
                $result = $syncService->syncFromOnline();
                if ($result) {
                    $this->info('✅ Sinkronisasi dari online berhasil');
                } else {
                    $this->error('❌ Sinkronisasi dari online gagal');
                }
                break;

            case 'both':
                $this->info('Sinkronisasi dua arah...');
                
                $this->info('1. Sinkronisasi ke online...');
                $toOnline = $syncService->syncToOnline();
                
                $this->info('2. Sinkronisasi dari online...');
                $fromOnline = $syncService->syncFromOnline();
                
                if ($toOnline && $fromOnline) {
                    $this->info('✅ Sinkronisasi dua arah berhasil');
                } else {
                    $this->error('❌ Sinkronisasi dua arah gagal');
                }
                break;

            default:
                $this->error('Direction tidak valid. Gunakan: to-online, from-online, atau both');
                return 1;
        }

        return 0;
    }
} 