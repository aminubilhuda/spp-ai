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
            'today' => Pembayaran::whereBetween('tanggal_bayar', [$startOfDay, $now])
                ->sum('jumlah_dibayar'),
            'week' => Pembayaran::whereBetween('tanggal_bayar', [$startOfWeek, $now])
                ->sum('jumlah_dibayar'),
            'month' => Pembayaran::whereBetween('tanggal_bayar', [$startOfMonth, $now])
                ->sum('jumlah_dibayar')
        ];

        // Get total outstanding payments
        $totalTagihan = TagihanDetail::sum('jumlah_biaya');
        $totalDibayar = Pembayaran::sum('jumlah_dibayar');
        $sisaTagihan = $totalTagihan - $totalDibayar;

        // Get payment status counts
        $statusCount = TagihanDetail::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();

        // Recent payments
        $recentPayments = Pembayaran::with(['tagihan.siswa', 'tagihan_detail'])
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