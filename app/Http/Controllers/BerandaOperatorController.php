<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TagihanDetail;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BerandaOperatorController extends Controller
{
    public function index()
    {
        // Payment statistics
        $now = Carbon::now();
        $startOfDay = $now->copy()->startOfDay();
        $startOfWeek = $now->copy()->startOfWeek();
        $startOfMonth = $now->copy()->startOfMonth();

        $paymentStats = [
            'today' => Pembayaran::where('status_konfirmasi', 'Sudah Dikonfirmasi')
                ->whereBetween('tanggal_bayar', [$startOfDay, $now])
                ->sum('jumlah_dibayar'),
            'week' => Pembayaran::where('status_konfirmasi', 'Sudah Dikonfirmasi')
                ->whereBetween('tanggal_bayar', [$startOfWeek, $now])
                ->sum('jumlah_dibayar'),
            'month' => Pembayaran::where('status_konfirmasi', 'Sudah Dikonfirmasi')
                ->whereBetween('tanggal_bayar', [$startOfMonth, $now])
                ->sum('jumlah_dibayar')
        ];

        // Get total outstanding payments
        $totalTagihan = TagihanDetail::sum('jumlah_biaya');
        $totalDibayar = Pembayaran::where('status_konfirmasi', 'Sudah Dikonfirmasi')->sum('jumlah_dibayar');
        $sisaTagihan = $totalTagihan - $totalDibayar;

        // Get payment status counts - hitung berdasarkan pembayaran yang sudah dikonfirmasi
        $statusCount = [];
        $tagihanDetails = TagihanDetail::with('pembayaran')->get();
        
        foreach ($tagihanDetails as $detail) {
            $totalDibayar = $detail->pembayaran->where('status_konfirmasi', 'Sudah Dikonfirmasi')->sum('jumlah_dibayar');
            $sisaBayar = $detail->jumlah_biaya - $totalDibayar;
            
            if ($sisaBayar <= 0) {
                $status = 'lunas';
            } elseif ($totalDibayar > 0) {
                $status = 'angsur';
            } else {
                $status = 'belum_lunas';
            }
            
            if (!isset($statusCount[$status])) {
                $statusCount[$status] = 0;
            }
            $statusCount[$status]++;
        }

        // Recent payments
        $recentPayments = Pembayaran::with(['tagihan.siswa', 'tagihan_detail'])
            ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
            ->latest('tanggal_bayar')
            ->take(5)
            ->get();

        $data = [
            'paymentStats' => $paymentStats,
            'sisaTagihan' => $sisaTagihan,
            'statusCount' => $statusCount,
            'recentPayments' => $recentPayments,
            'totalSiswa' => Siswa::count()
        ];

        return view('operator.beranda_index', $data);
    }
}