<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RestoreDatabaseCommand extends Command
{
    protected $signature = 'backup:restore {filename?}';
    protected $description = 'Restore database dari file backup SQL';

    public function handle()
    {
        $backupPath = storage_path('app/backup');
        
        // If no filename provided, list available backups
        if (!$this->argument('filename')) {
            $files = glob($backupPath . '/*.sql');
            if (empty($files)) {
                $this->error('Tidak ada file backup ditemukan!');
                return;
            }

            $backups = collect($files)->map(function ($file) {
                return basename($file);
            });

            $filename = $this->choice(
                'Pilih file backup untuk di-restore:',
                $backups->toArray()
            );
        } else {
            $filename = $this->argument('filename');
        }

        $filePath = $backupPath . '/' . $filename;
        
        if (!file_exists($filePath)) {
            $this->error('File backup tidak ditemukan: ' . $filename);
            return;
        }

        if (!$this->confirm('Ini akan menimpa database Anda saat ini. Anda yakin?')) {
            return;
        }

        // Get database configuration
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Construct mysql restore command
        $command = sprintf(
            'mysql -u%s -p%s %s < %s',
            $dbUser,
            $dbPass,
            $dbName,
            $filePath
        );

        // Execute restore command
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $this->info('Database berhasil di-restore dari: ' . $filename);
        } else {
            $this->error('Restore database gagal!');
        }
    }
}