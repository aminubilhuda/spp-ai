<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\Pembayaran;

class LaporanFormController extends Controller
{
    public function create(Request $request)
    {
        return view('operator.laporanform_index');
    }

    public function tagihan(Request $request)
    {
        $query = Tagihan::query()
            ->with(['siswa.jurusan', 'tagihanDetails']);

        // Filter berdasarkan status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan kelas
        if ($request->kelas) {
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }

        // Filter berdasarkan bulan
        if ($request->bulan) {
            $query->whereMonth('tanggal_tagihan', $request->bulan);
        }

        // Filter berdasarkan tahun
        if ($request->tahun) {
            $query->whereYear('tanggal_tagihan', $request->tahun);
        }

        $tagihan = $query->get();

        return view('operator.laporan.tagihan', compact('tagihan'));
    }

    public function pembayaran(Request $request)
    {
        $query = Pembayaran::query()
            ->with(['tagihan.siswa.jurusan', 'bankSekolah']);

        // Filter berdasarkan status pembayaran
        if ($request->status_pembayaran) {
            $query->where('status_konfirmasi', $request->status_pembayaran);
        }

        // Filter berdasarkan kelas
        if ($request->kelas) {
            $query->whereHas('tagihan.siswa', function($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }

        // Filter berdasarkan bulan
        if ($request->bulan) {
            $query->whereMonth('tanggal_bayar', $request->bulan);
        }

        // Filter berdasarkan tahun
        if ($request->tahun) {
            $query->whereYear('tanggal_bayar', $request->tahun);
        }

        $pembayaran = $query->get();

        return view('operator.laporan.pembayaran', compact('pembayaran'));
    }
}
