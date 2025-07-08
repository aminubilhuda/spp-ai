<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Services\WhatsappFonnteServices;

class WhatsappFonnteController extends Controller
{
    protected $whatsappService;
    public function __construct(WhatsappFonnteServices $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    // Endpoint tes kirim pesan tagihan custom
    public function testTagihan($tagihanId)
    {
        $tagihan = Tagihan::with(['siswa.wali'])->findOrFail($tagihanId);
        $result = $this->whatsappService->sendTagihanNotificationCustom($tagihan);
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Pesan WhatsApp berhasil dikirim',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesan WhatsApp'
            ], 500);
        }
    }

    /**
     * Tampilkan halaman pengaturan WhatsApp
     */
    public function settings()
    {
        return view('operator.whatsapp.settings');
    }

    /**
     * Simpan pengaturan WhatsApp (simulasi, karena config tidak bisa diubah runtime)
     */
    public function updateSettings(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'typing' => 'nullable|boolean',
            'country_code' => 'nullable|string',
            'delay' => 'nullable|numeric',
            'notifications' => 'nullable|array',
        ]);

        // Simulasi simpan ke session (karena config/services.php tidak bisa diubah runtime)
        session()->flash('success', 'Pengaturan WhatsApp berhasil disimpan (simulasi, config tidak berubah).');
        return redirect()->back();
    }

    /**
     * Endpoint untuk test kirim pesan WhatsApp dari modal settings
     */
    public function test(Request $request)
    {
        $request->validate([
            'number' => 'required|string',
            'type' => 'required|string',
            'title' => 'nullable|string',
            'message' => 'nullable|string',
        ]);

        $number = $request->number;
        $type = $request->type;
        $result = null;

        if ($type === 'system') {
            $result = $this->whatsappService->sendSystemNotification($number, $request->title, $request->message, 'info');
        } elseif ($type === 'pembayaran') {
            $mockPembayaran = $this->createMockPembayaran($number);
            $result = $this->whatsappService->sendPembayaranNotification($mockPembayaran);
        } elseif ($type === 'reminder') {
            $mockTagihan = $this->createMockTagihan($number);
            $result = $this->whatsappService->sendReminderPembayaran($mockTagihan);
        } elseif ($type === 'konfirmasi') {
            $mockPembayaran = $this->createMockPembayaran($number);
            $result = $this->whatsappService->sendKonfirmasiPembayaran($mockPembayaran);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tipe test tidak valid.'
            ], 400);
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
                'message' => 'Gagal mengirim pesan.'
            ], 500);
        }
    }

    // Mock data untuk test (mirip TestWhatsappService)
    protected function createMockPembayaran($number)
    {
        $mockPembayaran = new \stdClass();
        $mockPembayaran->id = 999;
        $mockPembayaran->jumlah_dibayar = 500000;
        $mockPembayaran->metode_pembayaran = 'Transfer Bank';
        $mockPembayaran->tanggal_bayar = now();
        $mockPembayaran->status = 'dikonfirmasi';

        $mockTagihan = new \stdClass();
        $mockTagihan->id = 999;
        $mockTagihan->total_tagihan = 500000;
        $mockTagihan->tenggat_waktu = now()->addDays(7);

        $mockSiswa = new \stdClass();
        $mockSiswa->id = 999;
        $mockSiswa->nama = 'Siswa Test';
        $mockSiswa->kelas = 'XII IPA 1';

        $mockWali = new \stdClass();
        $mockWali->id = 999;
        $mockWali->name = 'Wali Test';
        $mockWali->no_wa = $number;

        $mockSiswa->wali = $mockWali;
        $mockTagihan->siswa = $mockSiswa;
        $mockPembayaran->tagihan = $mockTagihan;

        return $mockPembayaran;
    }

    protected function createMockTagihan($number)
    {
        $mockTagihan = new \stdClass();
        $mockTagihan->id = 999;
        $mockTagihan->total_tagihan = 500000;
        $mockTagihan->tenggat_waktu = now()->addDays(7);

        $mockSiswa = new \stdClass();
        $mockSiswa->id = 999;
        $mockSiswa->nama = 'Siswa Test';
        $mockSiswa->kelas = 'XII IPA 1';

        $mockWali = new \stdClass();
        $mockWali->id = 999;
        $mockWali->name = 'Wali Test';
        $mockWali->no_wa = $number;

        $mockSiswa->wali = $mockWali;
        $mockTagihan->siswa = $mockSiswa;

        return $mockTagihan;
    }
}
