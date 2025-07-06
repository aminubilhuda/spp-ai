<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\Siswa;
use App\Models\BankSekolah;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class WaliMuridInvoiceController extends Controller
{
    public function show($id)
    {
        // Ambil data tagihan beserta relasinya
        $tagihan = Tagihan::with(['siswa.wali', 'tagihan_details'])
            ->findOrFail($id);
            
        // Ambil semua tagihan siswa yang sama
        $semuaTagihan = Tagihan::with(['tagihan_details'])
            ->where('siswa_id', $tagihan->siswa_id)
            ->get();
            
        // Hitung total tagihan
        $totalTagihan = 0;
        foreach ($semuaTagihan as $item) {
            $totalTagihan += $item->tagihan_details->sum('jumlah_biaya');
        }
        
        // Ambil data bank sekolah
        $bankSekolah = BankSekolah::with('bank')->get();
        
        $data = [
            'title' => 'Invoice Tagihan',
            'tagihan' => $tagihan,
            'semuaTagihan' => $semuaTagihan,
            'totalTagihan' => $totalTagihan,
            'bankSekolah' => $bankSekolah,
            'tanggal' => Carbon::now()->translatedFormat('d F Y'),
            'invoiceId' => 'INV/' . date('Ymd') . '/' . $tagihan->id,
        ];

        if (request('output') == 'pdf') {
            $pdf = PDF::loadView('wali.invoice_tagihan', $data);
            return $pdf->download('invoice_tagihan.pdf');
        }
        
        return view('wali.invoice_tagihan', $data);
    }
}
