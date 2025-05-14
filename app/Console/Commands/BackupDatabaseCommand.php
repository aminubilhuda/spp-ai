<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'backup:run';
    protected $description = 'Backup database ke file SQL';

    public function handle()
    {
        // Get database configuration
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Create backup directory if not exists
        $backupPath = storage_path('app/backup');
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // Generate backup filename with timestamp
        $filename = 'backup_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
        $filePath = $backupPath . '/' . $filename;

        // Construct mysqldump command
        $command = sprintf(
            'mysqldump -u%s -p%s %s > %s',
            $dbUser,
            $dbPass,
            $dbName,
            $filePath
        );

        // Execute backup command
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $this->info('Backup database berhasil dibuat: ' . $filename);
        } else {
            $this->error('Backup database gagal!');
        }
    }
}