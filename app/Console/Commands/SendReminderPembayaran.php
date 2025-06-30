<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tagihan;
use App\Services\WhatsappFonnteServices;
use Carbon\Carbon;

class SendReminderPembayaran extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:send-reminder 
                            {--days=7 : Jumlah hari sebelum tenggat waktu untuk mengirim reminder}
                            {--test : Mode test, tidak benar-benar mengirim pesan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim reminder pembayaran SPP via WhatsApp';

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
        $days = $this->option('days');
        $isTest = $this->option('test');

        $this->info("Mencari tagihan yang akan jatuh tempo dalam {$days} hari...");

        // Cari tagihan yang belum lunas dan akan jatuh tempo
        $tagihanList = Tagihan::with(['siswa.wali'])
            ->where('status', '!=', 'lunas')
            ->where('tenggat_waktu', '<=', Carbon::now()->addDays($days))
            ->where('tenggat_waktu', '>', Carbon::now())
            ->get();

        if ($tagihanList->isEmpty()) {
            $this->info('Tidak ada tagihan yang perlu di-remind.');
            return;
        }

        $this->info("Ditemukan {$tagihanList->count()} tagihan yang perlu di-remind.");

        $successCount = 0;
        $failedCount = 0;

        foreach ($tagihanList as $tagihan) {
            $this->line("Memproses tagihan ID: {$tagihan->id} - {$tagihan->siswa->nama}");

            // Cek apakah wali memiliki nomor WhatsApp
            if (!$tagihan->siswa->wali || !$tagihan->siswa->wali->no_wa) {
                $this->warn("  ⚠️  Wali tidak memiliki nomor WhatsApp");
                $failedCount++;
                continue;
            }

            if ($isTest) {
                $this->info("  🧪 TEST MODE: Akan mengirim reminder ke {$tagihan->siswa->wali->no_wa}");
                $successCount++;
            } else {
                // Kirim reminder
                $result = $this->whatsappService->sendReminderPembayaran($tagihan);
                
                if ($result) {
                    $this->info("  ✅ Reminder berhasil dikirim ke {$tagihan->siswa->wali->no_wa}");
                    $successCount++;
                } else {
                    $this->error("  ❌ Gagal mengirim reminder ke {$tagihan->siswa->wali->no_wa}");
                    $failedCount++;
                }
            }

            // Delay untuk menghindari spam
            if (!$isTest) {
                sleep(2);
            }
        }

        $this->newLine();
        $this->info("=== RINGKASAN ===");
        $this->info("Total tagihan diproses: " . $tagihanList->count());
        $this->info("Berhasil: {$successCount}");
        $this->info("Gagal: {$failedCount}");

        if ($isTest) {
            $this->warn("⚠️  MODE TEST - Tidak ada pesan yang benar-benar dikirim");
        }
    }
} 