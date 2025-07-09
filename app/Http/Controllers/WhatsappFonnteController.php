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
        // Ambil semua setting WhatsApp dari DB
        $waSettings = [
            'enabled' => settings('whatsapp_enabled', false),
            'token' => settings('whatsapp_token'),
            'typing' => settings('whatsapp_typing', false),
            'country_code' => settings('whatsapp_country_code', '62'),
            'delay' => settings('whatsapp_delay', '2'),
            'notif_pembayaran' => settings('whatsapp_notif_pembayaran', true),
            'notif_reminder' => settings('whatsapp_notif_reminder', true),
            'notif_konfirmasi' => settings('whatsapp_notif_konfirmasi', true),
            'notif_sistem' => settings('whatsapp_notif_sistem', true),
        ];
        return view('operator.whatsapp.settings', compact('waSettings'));
    }

    /**
     * Simpan pengaturan WhatsApp ke tabel settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'token' => 'nullable|string',
            'typing' => 'nullable|boolean',
            'country_code' => 'nullable|string',
            'delay' => 'nullable|numeric',
            'notifications' => 'nullable|array',
        ]);

        // Simpan ke tabel settings
        settings(['whatsapp_enabled' => $request->boolean('enabled')]);
        settings(['whatsapp_token' => $request->token]);
        settings(['whatsapp_typing' => $request->boolean('typing')]);
        settings(['whatsapp_country_code' => $request->country_code]);
        settings(['whatsapp_delay' => $request->delay]);
        $notifs = $request->notifications ?? [];
        settings(['whatsapp_notif_pembayaran' => isset($notifs['pembayaran'])]);
        settings(['whatsapp_notif_reminder' => isset($notifs['reminder'])]);
        settings(['whatsapp_notif_konfirmasi' => isset($notifs['konfirmasi'])]);
        settings(['whatsapp_notif_sistem' => isset($notifs['sistem'])]);

        session()->flash('success', 'Pengaturan WhatsApp berhasil disimpan!');
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
