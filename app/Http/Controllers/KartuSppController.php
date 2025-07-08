<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\TagihanDetail;
use App\Models\Biaya;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use App\Models\TahunPelajaran;

class KartuSppController extends Controller
{
    public function show($siswa_id)
    {
        $siswa = Siswa::findOrFail($siswa_id);

        // Ambil tahun pelajaran aktif
        $tahunAktif = TahunPelajaran::where('is_aktif', 1)->first();
        $tahunPelajaranId = request('tahun_pelajaran_id') ?? $tahunAktif?->id;
        $tahunPelajaran = TahunPelajaran::find($tahunPelajaranId);
        $tahun_ajaran = $tahunPelajaran ? $tahunPelajaran->nama : ($tahunAktif?->nama ?? '');

        // Ambil semua tagihan siswa di tahun pelajaran yang dipilih
        $tagihanSiswa = \App\Models\Tagihan::where('siswa_id', $siswa_id)
            ->when($tahunPelajaranId, function($q) use ($tahunPelajaranId) {
                $q->where('tahun_pelajaran_id', $tahunPelajaranId);
            })
            ->with('tagihan_details')
            ->get();

        // Gabungkan semua detail tagihan
        $tagihan_details = $tagihanSiswa->flatMap->tagihan_details;

        // Kelompokkan tagihan per bulan
        $tagihan_per_bulan = [];
        foreach ($tagihan_details as $detail) {
            $bulan = Carbon::parse($detail->tagihan->tanggal_tagihan)->month;
            if (!isset($tagihan_per_bulan[$bulan])) {
                $tagihan_per_bulan[$bulan] = [
                    'total_tagihan' => 0,
                    'items' => [],
                    'tanggal_bayar' => '',
                    'status' => 'Belum Lunas'
                ];
            }
            $tagihan_per_bulan[$bulan]['items'][] = [
                'nama_biaya' => $detail->nama_biaya,
                'jumlah_biaya' => $detail->jumlah_biaya,
                'status' => $detail->status_detail
            ];
            $tagihan_per_bulan[$bulan]['total_tagihan'] += $detail->jumlah_biaya;
            if ($detail->status_detail == 'Lunas' && empty($tagihan_per_bulan[$bulan]['tanggal_bayar'])) {
                $tagihan_per_bulan[$bulan]['tanggal_bayar'] = Carbon::parse($detail->created_at)->format('d/m/y');
            }
            $semua_lunas = collect($tagihan_per_bulan[$bulan]['items'])->every(function($item) {
                return $item['status'] == 'Lunas';
            });
            if ($semua_lunas) {
                $tagihan_per_bulan[$bulan]['status'] = 'Lunas';
            }
        }

        // Log untuk verifikasi
        Log::info('Tagihan yang diambil:', [
            'siswa' => $siswa->nama,
            'jumlah_tagihan' => $tagihan_details->count(),
            'detail_per_bulan' => $tagihan_per_bulan,
            'tahun_ajaran' => $tahun_ajaran
        ]);

        $pdf = PDF::loadView('operator.kartu_spp', [
            'siswa' => $siswa,
            'tagihan_per_bulan' => $tagihan_per_bulan,
            'tahun_ajaran' => $tahun_ajaran,
            'tahunAktif' => $tahunPelajaran
        ]);
        return $pdf->stream('kartu_spp_' . $siswa->nama . '.pdf');
    }
}
