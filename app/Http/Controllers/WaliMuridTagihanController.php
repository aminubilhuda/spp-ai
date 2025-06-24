<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WaliMuridTagihanController extends Controller
{
    public function index(Request $request)
    {
        $data['title'] = "Data Tagihan";
        $siswa = Auth::user()->siswa->pluck('id');
        $data['tagihan'] = Tagihan::with(['siswa', 'tagihan_details'])->whereIn('siswa_id', $siswa)->get();
        return view('wali.tagihan_index', $data);
    }

    public function show($id)
    {
        $siswa = Auth::user()->siswa->pluck('id');
        $tagihan = Tagihan::with(['siswa', 'tagihan_details'])->whereIn('siswa_id', $siswa)->findOrFail($id);
        
        $data['title'] = "Detail Tagihan";
        $data['tagihan'] = $tagihan;
        
        return view('wali.tagihan_show', $data);
    }

    public function getDetails($id)
    {
        try {
            $siswa = Auth::user()->siswa->pluck('id');
            $tagihan = Tagihan::with(['siswa', 'tagihan_details.pembayaran'])
                ->whereIn('siswa_id', $siswa)
                ->findOrFail($id);

            $totalTagihan = $tagihan->tagihan_details->sum('jumlah_biaya');

            $details = $tagihan->tagihan_details->map(function ($detail) {
                // Hitung total pembayaran yang sudah dikonfirmasi
                $totalDibayar = $detail->pembayaran()->where('status_konfirmasi', 'Sudah Dikonfirmasi')->sum('jumlah_dibayar');
                $sisaBayar = $detail->jumlah_biaya - $totalDibayar;
                
                return [
                    'id' => $detail->id,
                    'nama_biaya' => $detail->nama_biaya,
                    'jumlah_biaya' => (float) $detail->jumlah_biaya,
                    'status' => $detail->status,
                    'sisa_bayar' => max(0, $sisaBayar),
                    'pembayaran' => $detail->pembayaran->map(function ($pembayaran) {
                        return [
                            'jumlah_dibayar' => (float) $pembayaran->jumlah_dibayar,
                            'status_konfirmasi' => $pembayaran->status_konfirmasi
                        ];
                    })
                ];
            });

            $response = [
                'success' => true,
                'details' => $details,
                'total_tagihan' => (float) $totalTagihan,
                'siswa' => [
                    'id' => $tagihan->siswa->id,
                    'nama' => $tagihan->siswa->nama
                ]
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan saat mengambil data tagihan: ' . $e->getMessage()
            ], 500);
        }
    }
}