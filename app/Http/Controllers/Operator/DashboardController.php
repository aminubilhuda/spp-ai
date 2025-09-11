<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Models\PengeluaranKas;
use App\Models\Tagihan;
use App\Models\TahunPelajaran;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil tahun pelajaran aktif
        $tahunAktif = TahunPelajaran::where('is_aktif', 1)->first();
        $tahunPelajaranId = $tahunAktif?->id;

        $totalSiswa = Siswa::count();
        $totalTagihanBulanIni = Tagihan::whereMonth('tanggal_tagihan', now()->month)
                                        ->whereYear('tanggal_tagihan', now()->year)
                                        ->count();
        $totalPembayaranTerkonfirmasi = Pembayaran::where('status_konfirmasi', 'Sudah Dikonfirmasi')->sum('jumlah_dibayar');
        $totalKasKeluar = PengeluaranKas::sum('jumlah');
        
        // Menghitung total tagihan siswa dalam setahun berdasarkan tahun pelajaran aktif
        $totalTagihanSetahun = DB::table('tagihan_details')
            ->join('tagihans', 'tagihan_details.tagihan_id', '=', 'tagihans.id')
            ->where('tagihans.tahun_pelajaran_id', $tahunPelajaranId)
            ->sum('tagihan_details.jumlah_biaya');

        // Rincian tagihan berdasarkan jenis biaya
        $rincianTagihan = DB::table('tagihan_details')
            ->join('tagihans', 'tagihan_details.tagihan_id', '=', 'tagihans.id')
            ->join('biayas', 'tagihan_details.biaya_id', '=', 'biayas.id')
            ->whereYear('tagihans.tanggal_tagihan', now()->year)
            ->select(
                'biayas.nama as nama_biaya',
                DB::raw('SUM(tagihan_details.jumlah_biaya) as total_biaya'),
                DB::raw('COUNT(DISTINCT tagihans.siswa_id) as jumlah_siswa')
            )
            ->groupBy('biayas.id', 'biayas.nama')
            ->orderBy('total_biaya', 'desc')
            ->get();

        // Statistik pembayaran tepat waktu vs terlambat
        $pembayaranStats = Pembayaran::join('tagihans', 'pembayarans.tagihan_id', '=', 'tagihans.id')
            ->where('pembayarans.status_konfirmasi', 'Sudah Dikonfirmasi')
            ->whereYear('pembayarans.tanggal_bayar', now()->year)
            ->select(
                DB::raw('COUNT(CASE WHEN pembayarans.tanggal_bayar <= tagihans.tanggal_jatuh_tempo THEN 1 END) as tepat_waktu'),
                DB::raw('COUNT(CASE WHEN pembayarans.tanggal_bayar > tagihans.tanggal_jatuh_tempo THEN 1 END) as terlambat')
            )
            ->first();
        $pembayaranMenungguKonfirmasi = Pembayaran::where('status_konfirmasi', 'Belum Dikonfirmasi')
                                                    ->with('tagihan.siswa')
                                                    ->latest()
                                                    ->take(5)
                                                    ->get();

        // Data untuk grafik
        $dataPembayaran = Pembayaran::select(
            DB::raw('MONTH(tanggal_bayar) as bulan'),
            DB::raw('SUM(jumlah_dibayar) as total')
        )
        ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
        ->whereYear('tanggal_bayar', now()->year)
        ->groupBy('bulan')
        ->orderBy('bulan')
        ->get();

        $labels = [];
        $data = [];
        $bulanLabels = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        // Initialize all months with 0
        for ($i = 1; $i <= 12; $i++) {
            $labels[] = $bulanLabels[$i];
            $data[$i] = 0;
        }

        foreach ($dataPembayaran as $pembayaran) {
            $data[$pembayaran->bulan] = $pembayaran->total;
        }

        return view('operator.dashboard_index', [
            'title' => 'Dashboard Operator',
            'tahunAktif' => $tahunAktif,
            'totalSiswa' => $totalSiswa,
            'totalTagihanBulanIni' => $totalTagihanBulanIni,
            'totalPembayaranTerkonfirmasi' => $totalPembayaranTerkonfirmasi,
            'totalKasKeluar' => $totalKasKeluar,
            'totalTagihanSetahun' => $totalTagihanSetahun,
            'rincianTagihan' => $rincianTagihan,
            'pembayaranTepatWaktu' => $pembayaranStats->tepat_waktu ?? 0,
            'pembayaranTerlambat' => $pembayaranStats->terlambat ?? 0,
            'pembayaranMenungguKonfirmasi' => $pembayaranMenungguKonfirmasi,
            'chartLabels' => $labels,
            'chartData' => array_values($data),
        ]);
    }
}
