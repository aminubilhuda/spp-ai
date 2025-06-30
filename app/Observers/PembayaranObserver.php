<?php

namespace App\Observers;

use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PembayaranNotification;
use App\Services\WhatsappFonnteServices;

class PembayaranObserver
{
    protected $whatsappService;

    public function __construct(WhatsappFonnteServices $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Handle the Pembayaran "created" event.
     */
    public function created(Pembayaran $pembayaran): void
    {
        $this->syncBuktiPembayaran($pembayaran);
        
        // Load user relationship
        $pembayaran->load('user');
        
        // Kirim notifikasi database
        $pembayaran->tagihan->siswa->wali->notify(new PembayaranNotification($pembayaran));
        
        // Kirim notifikasi WhatsApp
        $this->whatsappService->sendPembayaranNotification($pembayaran);
        
        // Notifikasi untuk wali sekarang dikirim manual di controller
        // untuk menghindari notifikasi ganda ketika multiple pembayaran dibuat
        // Observer hanya aktif untuk pembayaran yang dibuat oleh admin/operator
        if ($pembayaran->user && in_array($pembayaran->user->akses, ['admin', 'operator'])) {
            $this->sendNotificationToOperators($pembayaran);
        }
    }

    /**
     * Handle the Pembayaran "updated" event.
     */
    public function updated(Pembayaran $pembayaran): void
    {
        $this->syncBuktiPembayaran($pembayaran);
        
        // Jika status pembayaran berubah menjadi dikonfirmasi
        if ($pembayaran->wasChanged('status') && $pembayaran->status === 'dikonfirmasi') {
            $this->whatsappService->sendKonfirmasiPembayaran($pembayaran);
        }
    }

    /**
     * Send notification to all operators
     */
    private function sendNotificationToOperators(Pembayaran $pembayaran): void
    {
        // Load relationships to avoid N+1 queries
        $pembayaran->load(['tagihan.siswa.wali', 'tagihan_detail', 'user']);
        
        // Ambil semua user dengan akses operator
        $operators = User::where('akses', 'operator')->get();
        
        // Kirim notifikasi ke semua operator
        Notification::send($operators, new PembayaranNotification($pembayaran));
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

    /**
     * Handle the Pembayaran "deleted" event.
     */
    public function deleted(Pembayaran $pembayaran): void
    {
        //
    }

    /**
     * Handle the Pembayaran "restored" event.
     */
    public function restored(Pembayaran $pembayaran): void
    {
        //
    }

    /**
     * Handle the Pembayaran "force deleted" event.
     */
    public function forceDeleted(Pembayaran $pembayaran): void
    {
        //
    }
} 