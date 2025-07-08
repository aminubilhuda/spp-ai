<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\TahunPelajaran;

class LaporanRekapPembayaranController extends Controller
{
    public function index(Request $request)
    {
        $tahunPelajarans = TahunPelajaran::orderByDesc('is_aktif')->orderBy('nama')->get();
        $tahunAktif = $tahunPelajarans->firstWhere('is_aktif', 1);
        $tahunPelajaranId = $request->get('tahun_pelajaran_id', $tahunAktif?->id);

        $query = Pembayaran::query()
            ->with(['tagihan.siswa.jurusan', 'bank_sekolah', 'tahunPelajaran']);

        // Filter tahun pelajaran
        if ($tahunPelajaranId) {
            $query->where('tahun_pelajaran_id', $tahunPelajaranId);
        }

        // Filter status pembayaran
        if ($request->status_pembayaran) {
            $query->where('status_konfirmasi', $request->status_pembayaran);
        }

        // Filter kelas
        if ($request->kelas) {
            $query->whereHas('tagihan.siswa', function($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }

        // Filter bulan
        if ($request->bulan) {
            $query->whereMonth('tanggal_bayar', $request->bulan);
        }

        $pembayaran = $query->get();

        return view('operator.laporan.rekap_pembayaran', compact('pembayaran', 'tahunPelajarans', 'tahunAktif', 'tahunPelajaranId'));
    }
}
