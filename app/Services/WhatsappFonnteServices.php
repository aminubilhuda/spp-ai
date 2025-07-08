<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WhatsappFonnteServices
{
    protected $token;
    protected $baseUrl = 'https://api.fonnte.com/send';
    protected $countryCode = '62';
    protected $typing = false;
    protected $delay = '2';

    public function __construct()
    {
        $this->token = config('services.fonnte.token');
    }

    /**
     * Kirim pesan WhatsApp
     */
    public function sendMessage($target, $message, $options = [])
    {
        // Cek apakah WhatsApp notification aktif
        if (!$this->isWhatsAppEnabled()) {
            Log::info('WhatsApp notification disabled, skipping message to: ' . $target);
            return false;
        }

        $data = array_merge([
            'target' => $target,
            'message' => $message,
            'countryCode' => $this->countryCode,
            'typing' => $this->typing,
            'delay' => $this->delay,
        ], $options);

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token
            ])->post($this->baseUrl, $data);

            $result = $response->json();

            if ($result['status'] ?? false) {
                Log::info('WhatsApp message sent successfully', [
                    'target' => $target,
                    'message_id' => $result['id'] ?? null,
                    'request_id' => $result['requestid'] ?? null
                ]);
                return $result;
            } else {
                Log::error('WhatsApp message failed', [
                    'target' => $target,
                    'response' => $result
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp API error', [
                'target' => $target,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Kirim notifikasi pembayaran
     */
    public function sendPembayaranNotification($pembayaran)
    {
        if (!$this->isNotificationEnabled('pembayaran')) {
            return false;
        }

        $siswa = $pembayaran->tagihan->siswa;
        $wali = $siswa->wali;
        
        if (!$wali || !$wali->no_wa) {
            Log::warning('Wali tidak memiliki nomor WhatsApp', ['siswa_id' => $siswa->id]);
            return false;
        }

        $message = $this->formatPembayaranMessage($pembayaran);
        
        return $this->sendMessage($wali->no_wa, $message);
    }

    /**
     * Kirim reminder pembayaran
     */
    public function sendReminderPembayaran($tagihan)
    {
        if (!$this->isNotificationEnabled('reminder')) {
            return false;
        }

        $siswa = $tagihan->siswa;
        $wali = $siswa->wali;
        
        if (!$wali || !$wali->no_wa) {
            Log::warning('Wali tidak memiliki nomor WhatsApp', ['siswa_id' => $siswa->id]);
            return false;
        }

        $message = $this->formatReminderMessage($tagihan);
        
        return $this->sendMessage($wali->no_wa, $message);
    }

    /**
     * Kirim konfirmasi pembayaran
     */
    public function sendKonfirmasiPembayaran($pembayaran)
    {
        if (!$this->isNotificationEnabled('konfirmasi')) {
            return false;
        }

        $siswa = $pembayaran->tagihan->siswa;
        $wali = $siswa->wali;
        
        if (!$wali || !$wali->no_wa) {
            Log::warning('Wali tidak memiliki nomor WhatsApp', ['siswa_id' => $siswa->id]);
            return false;
        }

        $message = $this->formatKonfirmasiMessage($pembayaran);
        
        return $this->sendMessage($wali->no_wa, $message);
    }

    /**
     * Kirim notifikasi sistem
     */
    public function sendSystemNotification($target, $title, $message, $type = 'info')
    {
        if (!$this->isNotificationEnabled('sistem')) {
            return false;
        }

        $formattedMessage = $this->formatSystemMessage($title, $message, $type);
        
        return $this->sendMessage($target, $formattedMessage);
    }

    /**
     * Format pesan pembayaran
     */
    protected function formatPembayaranMessage($pembayaran)
    {
        $siswa = $pembayaran->tagihan->siswa;
        $jumlah = number_format($pembayaran->jumlah_dibayar, 0, ',', '.');
        $tanggal = $pembayaran->tanggal_bayar->format('d/m/Y H:i');
        
        return "💰 *NOTIFIKASI PEMBAYARAN SPP*\n\n" .
               "Halo {$siswa->wali->name},\n\n" .
               "Pembayaran SPP telah diterima:\n" .
               "• Siswa: {$siswa->nama}\n" .
               "• Kelas: {$siswa->kelas}\n" .
               "• Jumlah: Rp {$jumlah}\n" .
               "• Metode: {$pembayaran->metode_pembayaran}\n" .
               "• Tanggal: {$tanggal}\n\n" .
               "Terima kasih atas pembayaran Anda.\n" .
               "Semoga pendidikan anak Anda berjalan lancar! 📚✨";
    }

    /**
     * Format pesan reminder
     */
    protected function formatReminderMessage($tagihan)
    {
        $siswa = $tagihan->siswa;
        $totalTagihan = number_format($tagihan->total_tagihan, 0, ',', '.');
        $tenggatWaktu = $tagihan->tenggat_waktu ? $tagihan->tenggat_waktu->format('d/m/Y') : 'Belum ditentukan';
        
        return "⏰ *PENGINGAT PEMBAYARAN SPP*\n\n" .
               "Halo {$siswa->wali->name},\n\n" .
               "Ini adalah pengingat untuk pembayaran SPP:\n" .
               "• Siswa: {$siswa->nama}\n" .
               "• Kelas: {$siswa->kelas}\n" .
               "• Total Tagihan: Rp {$totalTagihan}\n" .
               "• Tenggat Waktu: {$tenggatWaktu}\n\n" .
               "Mohon segera lakukan pembayaran untuk menghindari keterlambatan.\n" .
               "Terima kasih atas perhatiannya! 🙏";
    }

    /**
     * Format pesan konfirmasi
     */
    protected function formatKonfirmasiMessage($pembayaran)
    {
        $siswa = $pembayaran->tagihan->siswa;
        $jumlah = number_format($pembayaran->jumlah_dibayar, 0, ',', '.');
        $tanggal = $pembayaran->tanggal_bayar->format('d/m/Y H:i');
        
        return "✅ *KONFIRMASI PEMBAYARAN SPP*\n\n" .
               "Halo {$siswa->wali->name},\n\n" .
               "Pembayaran SPP telah berhasil dikonfirmasi:\n" .
               "• Siswa: {$siswa->nama}\n" .
               "• Kelas: {$siswa->kelas}\n" .
               "• Jumlah: Rp {$jumlah}\n" .
               "• Tanggal: {$tanggal}\n" .
               "• Status: ✅ LUNAS\n\n" .
               "Pembayaran telah diproses dan diterima dengan baik.\n" .
               "Terima kasih! 🎉";
    }

    /**
     * Format pesan sistem
     */
    protected function formatSystemMessage($title, $message, $type = 'info')
    {
        $icon = $this->getSystemIcon($type);
        
        return "{$icon} *{$title}*\n\n" . $message;
    }

    /**
     * Get icon berdasarkan tipe notifikasi
     */
    protected function getSystemIcon($type)
    {
        $icons = [
            'info' => 'ℹ️',
            'success' => '✅',
            'warning' => '⚠️',
            'error' => '❌',
            'maintenance' => '🔧',
            'update' => '🔄'
        ];

        return $icons[$type] ?? 'ℹ️';
    }

    /**
     * Cek apakah WhatsApp notification aktif secara global
     */
    protected function isWhatsAppEnabled()
    {
        return config('services.fonnte.enabled', false) && !empty($this->token);
    }

    /**
     * Cek apakah notifikasi tertentu aktif
     */
    protected function isNotificationEnabled($type)
    {
        $config = config("services.fonnte.notifications.{$type}", true);
        return $this->isWhatsAppEnabled() && $config;
    }

    /**
     * Kirim pesan dengan file attachment
     */
    public function sendMessageWithFile($target, $message, $filePath, $filename = null)
    {
        if (!$this->isWhatsAppEnabled()) {
            return false;
        }

        $data = [
            'target' => $target,
            'message' => $message,
            'countryCode' => $this->countryCode,
            'typing' => $this->typing,
            'delay' => $this->delay,
        ];

        if ($filename) {
            $data['filename'] = $filename;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token
            ])->attach('file', file_get_contents($filePath), $filename ?? basename($filePath))
              ->post($this->baseUrl, $data);

            $result = $response->json();

            if ($result['status'] ?? false) {
                Log::info('WhatsApp message with file sent successfully', [
                    'target' => $target,
                    'file' => $filePath
                ]);
                return $result;
            } else {
                Log::error('WhatsApp message with file failed', [
                    'target' => $target,
                    'file' => $filePath,
                    'response' => $result
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp API error with file', [
                'target' => $target,
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Kirim pesan dengan URL attachment
     */
    public function sendMessageWithUrl($target, $message, $url, $filename = null)
    {
        if (!$this->isWhatsAppEnabled()) {
            return false;
        }

        $data = [
            'target' => $target,
            'message' => $message,
            'url' => $url,
            'countryCode' => $this->countryCode,
            'typing' => $this->typing,
            'delay' => $this->delay,
        ];

        if ($filename) {
            $data['filename'] = $filename;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token
            ])->post($this->baseUrl, $data);

            $result = $response->json();

            if ($result['status'] ?? false) {
                Log::info('WhatsApp message with URL sent successfully', [
                    'target' => $target,
                    'url' => $url
                ]);
                return $result;
            } else {
                Log::error('WhatsApp message with URL failed', [
                    'target' => $target,
                    'url' => $url,
                    'response' => $result
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp API error with URL', [
                'target' => $target,
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Set delay untuk pesan
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;
        return $this;
    }

    /**
     * Set typing indicator
     */
    public function setTyping($typing)
    {
        $this->typing = $typing;
        return $this;
    }

    /**
     * Set country code
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * Format custom pesan tagihan SPP
     */
    public function formatTagihanMessage($tagihan)
    {
        $siswa = $tagihan->siswa;
        $total = number_format($tagihan->jumlah_tagihan, 0, ',', '.');
        $jatuhTempo = \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d/m/Y');

        return "\uD83D\uDCE2 *TAGIHAN SPP BARU*\n\n" .
               "Nama Siswa : {$siswa->nama}\n" .
               "Kelas      : {$siswa->kelas}\n" .
               "Total      : Rp {$total}\n" .
               "Jatuh Tempo: {$jatuhTempo}\n\n" .
               "Silakan segera melakukan pembayaran sebelum jatuh tempo.";
    }

    /**
     * Kirim pesan tagihan custom ke wali
     */
    public function sendTagihanNotificationCustom($tagihan)
    {
        if (!$this->isNotificationEnabled('sistem')) {
            return false;
        }
        $siswa = $tagihan->siswa;
        $wali = $siswa->wali;
        if (!$wali || !$wali->no_wa) {
            \Log::warning('Wali tidak memiliki nomor WhatsApp', ['siswa_id' => $siswa->id]);
            return false;
        }
        $message = $this->formatTagihanMessage($tagihan);
        return $this->sendMessage($wali->no_wa, $message);
    }
}
