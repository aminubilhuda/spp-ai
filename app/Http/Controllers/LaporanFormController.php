<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\TahunPelajaran;
use App\Models\Siswa;
use Carbon\Carbon;

class LaporanFormController extends Controller
{
    public function create(Request $request)
    {
        $tahunPelajarans = TahunPelajaran::orderByDesc('is_aktif')->orderBy('nama')->get();
        $kelasList = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        $totalHariIni = \App\Models\Pembayaran::where('status_konfirmasi', 'Sudah Dikonfirmasi')
            ->whereDate('tanggal_bayar', Carbon::today())
            ->sum('jumlah_dibayar');

        $totalMingguIni = \App\Models\Pembayaran::where('status_konfirmasi', 'Sudah Dikonfirmasi')
            ->whereBetween('tanggal_bayar', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('jumlah_dibayar');

        $totalBulanIni = \App\Models\Pembayaran::where('status_konfirmasi', 'Sudah Dikonfirmasi')
            ->whereBetween('tanggal_bayar', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('jumlah_dibayar');

        return view('operator.laporanform_index', compact(
            'tahunPelajarans',
            'totalHariIni',
            'totalMingguIni',
            'totalBulanIni',
            'kelasList'
        ));
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
