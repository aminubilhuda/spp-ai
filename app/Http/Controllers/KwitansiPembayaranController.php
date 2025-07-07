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
use PDF;
// use App\Models\Sekolah;

class KwitansiPembayaranController extends Controller
{
    public function show(Request $request, $id) 
    {
        // Ambil pembayaran dengan relasi
        $pembayaran = Pembayaran::with(['tagihan.siswa.jurusan', 'tagihan_detail', 'bank_sekolah'])
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
        
        // Check if PDF is requested
        if ($request->has('format') && $request->format === 'pdf') {
            $pdf = PDF::loadView('operator.kwitansi_pembayaran', compact(
                'pembayaran',
                'total_tagihan',
                'total_sudah_bayar',
                'sisa_bayar',
                'tagihan_detail',
                'nomor_cetak'
            ));
            
            $pdf->setPaper('A4', 'portrait');
            return $pdf->stream('kwitansi_' . $pembayaran->tagihan->siswa->nama . '.pdf');
        }
        
        return view('operator.kwitansi_pembayaran', compact(
            'pembayaran',
            'total_tagihan',
            'total_sudah_bayar',
            'sisa_bayar',
            'tagihan_detail',
            'nomor_cetak'
        ));
        
    }

    /**
     * Menampilkan kwitansi untuk pembayaran serentak
     */
    public function showBatch(Request $request)
    {
        try {
            // Ambil pembayaran_ids dari query string (GET) atau body (POST)
            $pembayaranIds = $request->input('pembayaran_ids', $request->query('pembayaran_ids', []));
            if (!is_array($pembayaranIds)) {
                $pembayaranIds = [$pembayaranIds];
            }

            // Validasi input
            $request->merge(['pembayaran_ids' => $pembayaranIds]);
            $request->validate([
                'pembayaran_ids' => 'required|array|min:1',
                'pembayaran_ids.*' => 'exists:pembayarans,id'
            ]);

            // Ambil semua pembayaran yang terkait
            $pembayaranList = Pembayaran::with([
                'tagihan.siswa.jurusan', 
                'tagihan_detail', 
                'bank_sekolah'
            ])->whereIn('id', $pembayaranIds)->get();

            if ($pembayaranList->isEmpty()) {
                return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan');
            }

            // Ambil pembayaran pertama sebagai referensi (semua pembayaran seharusnya untuk siswa yang sama)
            $pembayaran = $pembayaranList->first();

            // Validasi bahwa semua pembayaran adalah untuk siswa yang sama
            $siswaId = $pembayaran->tagihan->siswa_id;
            foreach ($pembayaranList as $item) {
                if ($item->tagihan->siswa_id !== $siswaId) {
                    return redirect()->back()->with('error', 'Pembayaran tidak valid - berbeda siswa');
                }
            }

            // Update pembayaran_id di tagihan_detail untuk pembayaran yang sudah dikonfirmasi
            foreach ($pembayaranList as $item) {
                if ($item->status_konfirmasi == 'Sudah Dikonfirmasi') {
                    $item->tagihan_detail->update(['pembayaran_id' => $item->id]);
                }
            }

            // Check if PDF is requested
            if ($request->has('format') && $request->format === 'pdf') {
                $pdf = PDF::loadView('operator.kwitansi_pembayaran_serentak', compact(
                    'pembayaran',
                    'pembayaranList',
                    'pembayaranIds'
                ));
                
                $pdf->setPaper('A4', 'portrait');
                return $pdf->stream('kwitansi_serentak_' . $pembayaran->tagihan->siswa->nama . '.pdf');
            }

            return view('operator.kwitansi_pembayaran_serentak', compact(
                'pembayaran',
                'pembayaranList',
                'pembayaranIds'
            ));

        } catch (\Exception $e) {
            \Log::error('Error in showBatch method:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Gagal menampilkan kwitansi: ' . $e->getMessage());
        }
    }
}
