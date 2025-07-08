<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BerandaWaliController extends Controller
{
    public function index()
    {
        $wali = auth()->user();
    // Ambil siswa yang diasuh wali (jika 1 wali bisa punya banyak siswa, sesuaikan)
    $siswa = $wali->siswa()->first(); // atau ->get() jika lebih dari satu

    // Status per bulan
    $status_per_bulan = [];
    foreach (bulanSPP() as $bulan) {
        $tagihan = \App\Models\TagihanDetail::whereHas('tagihan', function($q) use ($siswa, $bulan) {
            $q->where('siswa_id', $siswa->id)
              ->whereMonth('tanggal_tagihan', $bulan);
        })->first();

        $status_per_bulan[$bulan] = ($tagihan && $tagihan->status_detail == 'Lunas') ? 'LUNAS' : 'BELUM BAYAR';
    }

    // Notifikasi (misal dari relasi notifications)
    $notifikasi = $wali->notifications()->latest()->limit(5)->get();

    return view('wali.beranda_index', compact('siswa', 'status_per_bulan', 'notifikasi'));
    }
}