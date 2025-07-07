<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\TagihanDetail;
use App\Models\Biaya;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class KartuSppController extends Controller
{
    public function show($siswa_id)
    {
        $siswa = Siswa::findOrFail($siswa_id);

        // Ambil semua tagihan siswa
        $tagihan_details = TagihanDetail::whereHas('tagihan', function($q) use ($siswa_id) {
            $q->where('siswa_id', $siswa_id);
        })
        ->with(['tagihan', 'pembayaran'])
        ->get();

        // Kelompokkan tagihan per bulan
        $tagihan_per_bulan = [];
        foreach ($tagihan_details as $detail) {
            $bulan = Carbon::parse($detail->tagihan->tanggal_tagihan)->month;
            
            // Inisialisasi array untuk bulan jika belum ada
            if (!isset($tagihan_per_bulan[$bulan])) {
                $tagihan_per_bulan[$bulan] = [
                    'total_tagihan' => 0,
                    'items' => [],
                    'tanggal_bayar' => '',
                    'status' => 'Belum Lunas'
                ];
            }

            // Tambahkan detail tagihan
            $tagihan_per_bulan[$bulan]['items'][] = [
                'nama_biaya' => $detail->nama_biaya,
                'jumlah_biaya' => $detail->jumlah_biaya,
                'status' => $detail->status_detail
            ];

            // Tambahkan ke total tagihan
            $tagihan_per_bulan[$bulan]['total_tagihan'] += $detail->jumlah_biaya;

            // Update tanggal bayar jika sudah lunas
            if ($detail->status_detail == 'Lunas' && empty($tagihan_per_bulan[$bulan]['tanggal_bayar'])) {
                $tagihan_per_bulan[$bulan]['tanggal_bayar'] = Carbon::parse($detail->created_at)->format('d/m/y');
            }

            // Update status jika semua tagihan lunas
            $semua_lunas = collect($tagihan_per_bulan[$bulan]['items'])->every(function($item) {
                return $item['status'] == 'Lunas';
            });
            if ($semua_lunas) {
                $tagihan_per_bulan[$bulan]['status'] = 'Lunas';
            }
        }

        // Log untuk verifikasi
        Log::info('Tagihan yang diambil:', [
            'siswa' => $siswa->nama,
            'jumlah_tagihan' => $tagihan_details->count(),
            'detail_per_bulan' => $tagihan_per_bulan
        ]);

        // Tentukan tahun ajaran
        $bulan_sekarang = Carbon::now()->month;
        $tahun_sekarang = Carbon::now()->year;
        
        if ($bulan_sekarang >= 7) {
            $tahun_ajaran = $tahun_sekarang . '/' . ($tahun_sekarang + 1);
        } else {
            $tahun_ajaran = ($tahun_sekarang - 1) . '/' . $tahun_sekarang;
        }

        $pdf = PDF::loadView('operator.kartu_spp', compact('siswa', 'tagihan_per_bulan', 'tahun_ajaran'));
        return $pdf->stream('kartu_spp_' . $siswa->nama . '.pdf');
    }
}
