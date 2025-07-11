<?php

namespace App\Http\Controllers;

use App\Models\PengeluaranKas;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PengeluaranKasController extends Controller
{
    public function index(Request $request)
    {
        $query = PengeluaranKas::with('user')->orderBy('tanggal', 'desc');
        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori', $request->kategori);
        }
        if ($request->has('tanggal_dari') && $request->tanggal_dari != '') {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->has('tanggal_sampai') && $request->tanggal_sampai != '') {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        }
        $pengeluaran = $query->paginate(20)->withQueryString();
        return view('operator.pengeluaran_kas_index', [
            'pengeluaran' => $pengeluaran,
            'title' => 'Histori Pengeluaran Kas',
            'kategori' => $request->kategori,
            'tanggal_dari' => $request->tanggal_dari,
            'tanggal_sampai' => $request->tanggal_sampai
        ]);
    }

    public function create()
    {
        return view('operator.pengeluaran_kas_form', [
            'title' => 'Tambah Pengeluaran Kas',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:0',
            'kategori' => 'required|string|max:100',
            'keterangan' => 'nullable|string',
        ]);
        PengeluaranKas::create([
            'tanggal' => $request->tanggal,
            'jumlah' => $request->jumlah,
            'kategori' => $request->kategori,
            'keterangan' => $request->keterangan,
            'user_id' => Auth::id(),
        ]);
        return redirect()->route('pengeluaran-kas.index')->with('success', 'Pengeluaran kas berhasil dicatat');
    }

    public function laporanKas(Request $request)
    {
        // Total uang masuk (sudah dikonfirmasi)
        $totalMasuk = Pembayaran::where('status_konfirmasi', 'Sudah Dikonfirmasi')->sum('jumlah_dibayar');
        // Total pengeluaran
        $totalKeluar = PengeluaranKas::sum('jumlah');
        $saldo = $totalMasuk - $totalKeluar;
        $histori = PengeluaranKas::orderBy('tanggal', 'desc')->get();
        return view('operator.laporan_kas', [
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'saldo' => $saldo,
            'histori' => $histori,
            'title' => 'Laporan Kas Sekolah',
        ]);
    }
} 