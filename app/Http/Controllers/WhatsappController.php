<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WhatsappFonnteServices;
use Illuminate\Support\Facades\Cache;

class WhatsappController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsappFonnteServices $whatsappService)
    {
        $this->whatsappService = $whatsappService;
        $this->middleware('auth');
    }

    /**
     * Tampilkan halaman pengaturan WhatsApp
     */
    public function settings()
    {
        $settings = [
            'enabled' => config('services.fonnte.enabled'),
            'notifications' => config('services.fonnte.notifications'),
            'country_code' => config('services.fonnte.settings.country_code'),
            'typing' => config('services.fonnte.settings.typing'),
            'delay' => config('services.fonnte.settings.delay'),
        ];

        return view('operator.whatsapp.settings', compact('settings'));
    }

    /**
     * Update pengaturan WhatsApp
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'enabled' => 'boolean',
            'notifications.pembayaran' => 'boolean',
            'notifications.reminder' => 'boolean',
            'notifications.konfirmasi' => 'boolean',
            'notifications.sistem' => 'boolean',
            'country_code' => 'string|max:5',
            'typing' => 'boolean',
            'delay' => 'integer|min:0|max:60',
        ]);

        // Update cache untuk pengaturan runtime
        Cache::put('whatsapp.enabled', $request->enabled, 3600);
        Cache::put('whatsapp.notifications', $request->notifications, 3600);
        Cache::put('whatsapp.country_code', $request->country_code, 3600);
        Cache::put('whatsapp.typing', $request->typing, 3600);
        Cache::put('whatsapp.delay', $request->delay, 3600);

        return redirect()->back()->with('success', 'Pengaturan WhatsApp berhasil diperbarui!');
    }

    /**
     * Test WhatsApp service
     */
    public function test(Request $request)
    {
        $request->validate([
            'number' => 'required|string',
            'type' => 'required|in:system,pembayaran,reminder,konfirmasi',
            'title' => 'required_if:type,system|string',
            'message' => 'required_if:type,system|string',
        ]);

        try {
            $number = $request->number;
            $type = $request->type;

            switch ($type) {
                case 'system':
                    $result = $this->whatsappService->sendSystemNotification(
                        $number,
                        $request->title,
                        $request->message,
                        'info'
                    );
                    break;

                case 'pembayaran':
                    // Buat mock pembayaran untuk test
                    $mockPembayaran = $this->createMockPembayaran($number);
                    $result = $this->whatsappService->sendPembayaranNotification($mockPembayaran);
                    break;

                case 'reminder':
                    // Buat mock tagihan untuk test
                    $mockTagihan = $this->createMockTagihan($number);
                    $result = $this->whatsappService->sendReminderPembayaran($mockTagihan);
                    break;

                case 'konfirmasi':
                    // Buat mock pembayaran untuk test
                    $mockPembayaran = $this->createMockPembayaran($number);
                    $result = $this->whatsappService->sendKonfirmasiPembayaran($mockPembayaran);
                    break;
            }

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan berhasil dikirim!',
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim pesan'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kirim notifikasi sistem ke semua wali
     */
    public function sendSystemNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,success,warning,error,maintenance,update',
        ]);

        // Ambil semua wali yang memiliki nomor WhatsApp
        $walis = \App\Models\Wali::whereNotNull('no_wa')
            ->where('no_wa', '!=', '')
            ->get();

        $successCount = 0;
        $failedCount = 0;

        foreach ($walis as $wali) {
            $result = $this->whatsappService->sendSystemNotification(
                $wali->no_wa,
                $request->title,
                $request->message,
                $request->type
            );

            if ($result) {
                $successCount++;
            } else {
                $failedCount++;
            }

            // Delay untuk menghindari spam
            sleep(2);
        }

        return response()->json([
            'success' => true,
            'message' => "Notifikasi berhasil dikirim ke {$successCount} wali, gagal {$failedCount}",
            'data' => [
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'total_sent' => $walis->count()
            ]
        ]);
    }

    /**
     * Buat mock pembayaran untuk testing
     */
    protected function createMockPembayaran($number)
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
        $mockWali->no_wa = $number;

        $mockSiswa->wali = $mockWali;
        $mockTagihan->siswa = $mockSiswa;
        $mockPembayaran->tagihan = $mockTagihan;

        return $mockPembayaran;
    }

    /**
     * Buat mock tagihan untuk testing
     */
    protected function createMockTagihan($number)
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
        $mockWali->no_wa = $number;

        $mockSiswa->wali = $mockWali;
        $mockTagihan->siswa = $mockSiswa;

        return $mockTagihan;
    }
} 