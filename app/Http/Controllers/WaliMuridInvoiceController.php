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

        // Jika ada parameter 'html', tampilkan versi HTML
        if (request('html') == 'true' || request()->is('*/html')) {
            return view('wali.invoice_tagihan', $data);
        }

        // Jika ada parameter 'download', download PDF
        if (request('download') == 'true') {
            $pdf = $this->generatePDF($data);
            return $pdf->download('invoice_tagihan_' . $tagihan->siswa->nama . '.pdf');
        }

        // Default: tampilkan PDF di browser
        $pdf = $this->generatePDF($data);
        return $pdf->stream('invoice_tagihan_' . $tagihan->siswa->nama . '.pdf');
    }

    /**
     * Generate PDF dengan konfigurasi yang optimal
     */
    private function generatePDF($data)
    {
        $pdf = PDF::loadView('wali.invoice_tagihan', $data);
        
        // Konfigurasi DomPDF yang sederhana dan kompatibel
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial',
            'chroot' => public_path(),
            'default_paper_size' => 'a4',
            'dpi' => 96,
            'font_height_ratio' => 0.9,
            'enable_php' => false,
            'enable_javascript' => false,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
        ]);

        return $pdf;
    }
}
