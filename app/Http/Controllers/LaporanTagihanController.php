<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\TahunPelajaran;

class LaporanTagihanController extends Controller
{
    public function index(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_aktif', 1)->first();
        $tahunPelajaranId = $request->get('tahun_pelajaran_id', $tahunAktif?->id);

        $query = Tagihan::query()
            ->with(['siswa.jurusan', 'tagihan_details']);

        // Filter tahun pelajaran
        if ($tahunPelajaranId) {
            $query->where('tahun_pelajaran_id', $tahunPelajaranId);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->whereHas('tagihan_details', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Filter kelas
        if ($request->kelas) {
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }

        // Filter bulan
        if ($request->bulan) {
            $query->whereMonth('tanggal_tagihan', $request->bulan);
        }

        // Filter tahun
        if ($request->tahun) {
            $query->whereYear('tanggal_tagihan', $request->tahun);
        }

        $tagihan = $query->get();

        return view('operator.laporan.tagihan', compact('tagihan', 'tahunAktif', 'tahunPelajaranId'));
    }
}
