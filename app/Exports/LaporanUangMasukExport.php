<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanUangMasukExport implements FromCollection, WithHeadings, WithTitle
{
    protected $periode;

    public function __construct($periode)
    {
        $this->periode = $periode;
    }

    public function collection()
    {
        $query = Pembayaran::where('status_konfirmasi', 'Sudah Dikonfirmasi');
        if ($this->periode == 'hari') {
            $query->whereDate('tanggal_bayar', Carbon::today());
        } elseif ($this->periode == 'minggu') {
            $query->whereBetween('tanggal_bayar', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($this->periode == 'bulan') {
            $query->whereBetween('tanggal_bayar', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        }
        $pembayaran = $query->with(['tagihan.siswa'])->orderBy('tanggal_bayar', 'desc')->get();

        return $pembayaran->map(function($item) {
            return [
                'Tanggal' => Carbon::parse($item->tanggal_bayar)->format('d/m/Y'),
                'Siswa' => $item->tagihan && $item->tagihan->siswa ? $item->tagihan->siswa->nama : '-',
                'Jumlah' => $item->jumlah_dibayar,
                'Metode' => $item->metode_pembayaran,
                'Status' => 'Sudah Dikonfirmasi',
            ];
        });
    }

    public function headings(): array
    {
        return ['Tanggal', 'Siswa', 'Jumlah', 'Metode', 'Status'];
    }

    public function title(): string
    {
        if ($this->periode == 'hari') return 'Uang Masuk Hari Ini';
        if ($this->periode == 'minggu') return 'Uang Masuk Minggu Ini';
        if ($this->periode == 'bulan') return 'Uang Masuk Bulan Ini';
        return 'Laporan Uang Masuk';
    }
} 