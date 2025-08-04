<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Pembayaran;
use App\Models\PengeluaranKas;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSiswa = Siswa::count();
        $totalTagihanBulanIni = Tagihan::whereMonth('tanggal_tagihan', now()->month)
                                        ->whereYear('tanggal_tagihan', now()->year)
                                        ->count();
        $totalPembayaranTerkonfirmasi = Pembayaran::where('status_konfirmasi', 'Sudah Dikonfirmasi')->sum('jumlah_dibayar');
        $totalKasKeluar = PengeluaranKas::sum('jumlah');
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
            'totalSiswa' => $totalSiswa,
            'totalTagihanBulanIni' => $totalTagihanBulanIni,
            'totalPembayaranTerkonfirmasi' => $totalPembayaranTerkonfirmasi,
            'totalKasKeluar' => $totalKasKeluar,
            'pembayaranMenungguKonfirmasi' => $pembayaranMenungguKonfirmasi,
            'chartLabels' => $labels,
            'chartData' => array_values($data),
        ]);
    }
}
