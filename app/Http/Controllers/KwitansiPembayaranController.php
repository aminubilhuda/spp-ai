<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\TagihanDetail;
use App\Models\Biaya;
use App\Models\Wali;
use App\Models\Jurusan;
// use App\Models\Sekolah;

class KwitansiPembayaranController extends Controller
{
    public function show($id) 
    {
        // Ambil pembayaran dengan relasi
        $pembayaran = Pembayaran::with(['tagihan.siswa.jurusan', 'tagihan_detail'])
            ->findOrFail($id);
        
        // Update pembayaran_id di tagihan_detail jika ini pembayaran terkonfirmasi
        if ($pembayaran->status_konfirmasi == 'Sudah Dikonfirmasi') {
            $pembayaran->tagihan_detail->update(['pembayaran_id' => $pembayaran->id]);
        }
            
        // Hitung total tagihan dari detail tagihan
        $total_tagihan = $pembayaran->tagihan_detail->jumlah_biaya;
        
        // Ambil semua pembayaran yang sudah dikonfirmasi untuk tagihan_detail yang sama
        $all_payments = Pembayaran::where('tagihan_id', $pembayaran->tagihan_id)
            ->where('tagihan_detail_id', $pembayaran->tagihan_detail_id)
            ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
            ->orderBy('id', 'asc')
            ->get();
            
        // Hitung total yang sudah dibayar
        $total_sudah_bayar = $all_payments->sum('jumlah_dibayar');
            
        // Hitung sisa yang harus dibayar
        $sisa_bayar = max(0, $total_tagihan - $total_sudah_bayar);
        
        // Update status jika sudah lunas
        if ($total_sudah_bayar >= $total_tagihan) {
            $sisa_bayar = 0;
            $pembayaran->tagihan_detail->update(['status' => 'lunas']);
        }
        
        $tanggal_cetak = now()->format('Ymd');
        $nomor_cetak = sprintf("SMKAN-%s-%s", $pembayaran->id, $tanggal_cetak);

        // Get the specific TagihanDetail record for this payment
        $tagihan_detail = $pembayaran->tagihan_detail;
        
        return view('operator.kwitansi_pembayaran', compact(
            'pembayaran',
            'total_tagihan',
            'total_sudah_bayar',
            'sisa_bayar',
            'tagihan_detail',
            'nomor_cetak'
        ));
        
    }
}
