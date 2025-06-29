<?php

namespace App\Observers;

use App\Models\Pembayaran;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PembayaranObserver
{
    /**
     * Handle the Pembayaran "created" event.
     */
    public function created(Pembayaran $pembayaran): void
    {
        $this->syncBuktiPembayaran($pembayaran);
    }

    /**
     * Handle the Pembayaran "updated" event.
     */
    public function updated(Pembayaran $pembayaran): void
    {
        $this->syncBuktiPembayaran($pembayaran);
    }

    /**
     * Sync bukti pembayaran from storage to public
     */
    private function syncBuktiPembayaran(Pembayaran $pembayaran): void
    {
        if ($pembayaran->bukti_bayar) {
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
            }
        }
    }
} 