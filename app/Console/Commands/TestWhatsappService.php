<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsappFonnteServices;

class TestWhatsappService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test 
                            {number : Nomor WhatsApp untuk test (contoh: 08123456789)}
                            {--type=system : Tipe pesan (system, pembayaran, reminder, konfirmasi)}
                            {--title=Test : Judul pesan untuk tipe system}
                            {--message=Ini adalah pesan test : Isi pesan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test WhatsApp service dengan berbagai tipe pesan';

    protected $whatsappService;

    public function __construct(WhatsappFonnteServices $whatsappService)
    {
        parent::__construct();
        $this->whatsappService = $whatsappService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $number = $this->argument('number');
        $type = $this->option('type');
        $title = $this->option('title');
        $message = $this->option('message');

        $this->info("🧪 Testing WhatsApp Service");
        $this->line("Nomor: {$number}");
        $this->line("Tipe: {$type}");
        $this->line("Judul: {$title}");
        $this->line("Pesan: {$message}");
        $this->newLine();

        // Cek konfigurasi
        $this->info("📋 Cek Konfigurasi:");
        $this->line("  • WhatsApp Enabled: " . (config('services.fonnte.enabled') ? '✅' : '❌'));
        $this->line("  • Token: " . (config('services.fonnte.token') ? '✅ Set' : '❌ Tidak Set'));
        $this->line("  • Country Code: " . config('services.fonnte.settings.country_code', '62'));
        $this->line("  • Delay: " . config('services.fonnte.settings.delay', '2') . " detik");
        $this->newLine();

        // Cek notifikasi settings
        $this->info("🔔 Pengaturan Notifikasi:");
        $notifications = config('services.fonnte.notifications', []);
        foreach ($notifications as $key => $enabled) {
            $this->line("  • {$key}: " . ($enabled ? '✅' : '❌'));
        }
        $this->newLine();

        if (!config('services.fonnte.enabled')) {
            $this->error("❌ WhatsApp service tidak aktif. Set FONNTE_ENABLED=true di .env");
            return 1;
        }

        if (!config('services.fonnte.token')) {
            $this->error("❌ Token Fonnte tidak diset. Set FONNTE_TOKEN di .env");
            return 1;
        }

        // Kirim pesan sesuai tipe
        $this->info("📤 Mengirim pesan test...");
        
        try {
            switch ($type) {
                case 'system':
                    $result = $this->whatsappService->sendSystemNotification($number, $title, $message, 'info');
                    break;
                    
                case 'pembayaran':
                    // Buat mock pembayaran untuk test
                    $mockPembayaran = $this->createMockPembayaran();
                    $result = $this->whatsappService->sendPembayaranNotification($mockPembayaran);
                    break;
                    
                case 'reminder':
                    // Buat mock tagihan untuk test
                    $mockTagihan = $this->createMockTagihan();
                    $result = $this->whatsappService->sendReminderPembayaran($mockTagihan);
                    break;
                    
                case 'konfirmasi':
                    // Buat mock pembayaran untuk test
                    $mockPembayaran = $this->createMockPembayaran();
                    $result = $this->whatsappService->sendKonfirmasiPembayaran($mockPembayaran);
                    break;
                    
                default:
                    $this->error("❌ Tipe pesan tidak valid. Pilihan: system, pembayaran, reminder, konfirmasi");
                    return 1;
            }

            if ($result) {
                $this->info("✅ Pesan berhasil dikirim!");
                $this->line("  • Message ID: " . ($result['id'][0] ?? 'N/A'));
                $this->line("  • Request ID: " . ($result['requestid'] ?? 'N/A'));
                $this->line("  • Status: " . ($result['status'] ? 'Success' : 'Failed'));
                $this->line("  • Detail: " . ($result['detail'] ?? 'N/A'));
            } else {
                $this->error("❌ Gagal mengirim pesan");
            }

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Buat mock pembayaran untuk testing
     */
    protected function createMockPembayaran()
    {
        $mockPembayaran = new \stdClass();
        $mockPembayaran->id = 999;
        $mockPembayaran->jumlah_dibayar = 500000;
        $mockPembayaran->metode_pembayaran = 'Transfer Bank';
        $mockPembayaran->tanggal_bayar = now();
        $mockPembayaran->status = 'dikonfirmasi';

        // Mock tagihan
        $mockTagihan = new \stdClass();
        $mockTagihan->id = 999;
        $mockTagihan->total_tagihan = 500000;
        $mockTagihan->tenggat_waktu = now()->addDays(7);

        // Mock siswa
        $mockSiswa = new \stdClass();
        $mockSiswa->id = 999;
        $mockSiswa->nama = 'Siswa Test';
        $mockSiswa->kelas = 'XII IPA 1';

        // Mock wali
        $mockWali = new \stdClass();
        $mockWali->id = 999;
        $mockWali->name = 'Wali Test';
        $mockWali->no_wa = $this->argument('number');

        $mockSiswa->wali = $mockWali;
        $mockTagihan->siswa = $mockSiswa;
        $mockPembayaran->tagihan = $mockTagihan;

        return $mockPembayaran;
    }

    /**
     * Buat mock tagihan untuk testing
     */
    protected function createMockTagihan()
    {
        $mockTagihan = new \stdClass();
        $mockTagihan->id = 999;
        $mockTagihan->total_tagihan = 500000;
        $mockTagihan->tenggat_waktu = now()->addDays(7);

        // Mock siswa
        $mockSiswa = new \stdClass();
        $mockSiswa->id = 999;
        $mockSiswa->nama = 'Siswa Test';
        $mockSiswa->kelas = 'XII IPA 1';

        // Mock wali
        $mockWali = new \stdClass();
        $mockWali->id = 999;
        $mockWali->name = 'Wali Test';
        $mockWali->no_wa = $this->argument('number');

        $mockSiswa->wali = $mockWali;
        $mockTagihan->siswa = $mockSiswa;

        return $mockTagihan;
    }
} 