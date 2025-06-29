<?php

namespace App\Console\Commands;

use App\Models\Pembayaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncBuktiPembayaran extends Command
{
    protected $signature = 'pembayaran:sync-bukti';
    protected $description = 'Sinkronisasi file bukti pembayaran dari storage ke public';

    public function handle()
    {
        $this->info('Memulai sinkronisasi bukti pembayaran...');

        $pembayarans = Pembayaran::whereNotNull('bukti_bayar')->get();
        $count = 0;

        foreach ($pembayarans as $pembayaran) {
            $sourcePath = storage_path('app/public/' . $pembayaran->bukti_bayar);
            $destinationPath = public_path('storage/' . $pembayaran->bukti_bayar);

            // Pastikan folder tujuan ada
            $destinationDir = dirname($destinationPath);
            if (!File::exists($destinationDir)) {
                File::makeDirectory($destinationDir, 0755, true);
            }

            // Salin file jika ada
            if (File::exists($sourcePath)) {
                File::copy($sourcePath, $destinationPath);
                $count++;
            }
        }

        $this->info("Sinkronisasi selesai! {$count} file telah disinkronkan.");
    }
} 