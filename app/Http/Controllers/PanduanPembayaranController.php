<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\BankSekolah;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PanduanPembayaranController extends Controller
{
    public function index($id)
    {
        $tagihan = Tagihan::with(['siswa', 'tagihan_details'])->findOrFail($id);
        $bankSekolah = BankSekolah::with('bank')->get();
        
        $data = [
            'title' => 'Panduan Pembayaran',
            'tagihan' => $tagihan,
            'bankSekolah' => $bankSekolah,
        ];
        
        return view('wali.panduan_pembayaran', $data);
    }
}
