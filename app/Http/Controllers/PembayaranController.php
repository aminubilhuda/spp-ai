<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TagihanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembayaranController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Hapus middleware auth.operator untuk sementara
    }

    /**
     * Menyimpan data pembayaran baru
     * Mendukung pembayaran oleh wali (Bank Transfer) dan operator (Bank Transfer/Cash)
     */
    public function store(Request $request)
    {
        // Tentukan validasi berdasarkan user yang login
        $user = auth()->user();
        $isWali = $user->akses === 'wali';
        
        // Atur aturan validasi dasar
        $validationRules = [
            'tagihan_id' => 'required|exists:tagihans,id',
            'jumlah_dibayar' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:Bank Transfer,Cash',
            'tanggal_bayar' => 'required|date',
            'status_konfirmasi' => 'required|in:Belum Dikonfirmasi,Sudah Dikonfirmasi',
        ];

        // Jika wali, hanya boleh Bank Transfer dan bukti wajib
        if ($isWali) {
            $validationRules['detail_ids'] = 'required|array|min:1';
            $validationRules['detail_ids.*'] = 'exists:tagihan_details,id';
            $validationRules['metode_pembayaran'] = 'required|in:Bank Transfer';
            $validationRules['bukti_bayar'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
            $validationRules['status_konfirmasi'] = 'required|in:Belum Dikonfirmasi';
            $validationRules['bank_sekolah_id'] = 'required|exists:bank_sekolahs,id';
            $validationRules['no_rekening_pengirim'] = 'required|string|max:50';
            $validationRules['bank_pengirim'] = 'required|string|max:50';
        } else {
            // Jika operator/admin, bisa pilih metode dan bukti wajib jika Bank Transfer
            $validationRules['metode_pembayaran'] = 'required|in:Bank Transfer,Cash';
            $validationRules['bukti_bayar'] = 'required_if:metode_pembayaran,Bank Transfer|file|mimes:jpg,jpeg,png,pdf|max:2048';
            $validationRules['status_konfirmasi'] = 'required|in:Belum Dikonfirmasi,Sudah Dikonfirmasi';
            $validationRules['no_rekening_pengirim'] = 'required_if:metode_pembayaran,Bank Transfer|string|max:50|nullable';
            $validationRules['bank_pengirim'] = 'required_if:metode_pembayaran,Bank Transfer|string|max:50|nullable';
            
            // Operator bisa menggunakan detail_ids atau detail_id
            if ($request->has('detail_ids')) {
                $validationRules['detail_ids'] = 'required|array|min:1';
                $validationRules['detail_ids.*'] = 'exists:tagihan_details,id';
            } else {
                $validationRules['detail_id'] = 'required|exists:tagihan_details,id';
            }
        }

        $request->validate($validationRules);

        // Mulai transaksi database untuk memastikan konsistensi data
        DB::beginTransaction();
        try {
            // Ambil data tagihan dan validasi
            $tagihan = Tagihan::findOrFail($request->tagihan_id);
            
            // Validasi bahwa semua detail_ids milik tagihan yang sama
            $detailIds = $request->detail_ids ?? [$request->detail_id];
            $tagihanDetails = TagihanDetail::whereIn('id', $detailIds)
                ->where('tagihan_id', $tagihan->id)
                ->get();
                
            if ($tagihanDetails->count() !== count($detailIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Beberapa item tagihan tidak valid'
                ], 400);
            }

            // Hitung total sisa tagihan untuk item yang dipilih
            $totalRemaining = 0;
            foreach ($tagihanDetails as $detail) {
                $totalDibayar = $detail->pembayaran()
                    ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                    ->sum('jumlah_dibayar');
                $sisaBayar = $detail->jumlah_biaya - $totalDibayar;
                $totalRemaining += $sisaBayar;
            }

            // Validasi jumlah pembayaran tidak boleh melebihi sisa tagihan
            if ($request->jumlah_dibayar > $totalRemaining) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran melebihi sisa tagihan yang harus dibayar'
                ], 400);
            }

            // Handle upload file bukti pembayaran untuk Bank Transfer
            $buktiPath = null;
            if ($request->metode_pembayaran === 'Bank Transfer' && $request->hasFile('bukti_bayar')) {
                $file = $request->file('bukti_bayar');
                $buktiPath = $file->store('bukti_pembayaran', 'public');
            }

            // Buat record pembayaran untuk setiap item yang dipilih
            $pembayaranIds = [];
            $totalSisaTagihan = 0;
            
            // Hitung total sisa tagihan dari semua item yang dipilih
            foreach ($tagihanDetails as $detail) {
                $totalDibayarDetail = $detail->pembayaran()
                    ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                    ->sum('jumlah_dibayar');
                $sisaBayar = $detail->jumlah_biaya - $totalDibayarDetail;
                $sisaBayar = max(0, $sisaBayar);
                $totalSisaTagihan += $sisaBayar;
            }

            // Jika pembayaran parsial, hitung proporsi untuk setiap item
            $jumlahDibayar = $request->jumlah_dibayar;
            $isPembayaranParsial = $jumlahDibayar < $totalSisaTagihan;
            
            foreach ($tagihanDetails as $detail) {
                $totalDibayarDetail = $detail->pembayaran()
                    ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                    ->sum('jumlah_dibayar');
                $sisaBayar = $detail->jumlah_biaya - $totalDibayarDetail;
                $sisaBayar = max(0, $sisaBayar);
                
                // Hitung jumlah pembayaran untuk item ini
                if ($isPembayaranParsial && $totalSisaTagihan > 0) {
                    // Pembayaran parsial: bagi secara proporsional berdasarkan sisa tagihan
                    $proporsi = $sisaBayar / $totalSisaTagihan;
                    $jumlahUntukItem = $jumlahDibayar * $proporsi;
                } else {
                    // Pembayaran penuh atau sesuai sisa tagihan
                    $jumlahUntukItem = $sisaBayar;
                }

                // Buat record pembayaran baru
                $pembayaran = Pembayaran::create([
                    'tagihan_id' => $request->tagihan_id,
                    'tagihan_detail_id' => $detail->id,
                    'wali_id' => $tagihan->siswa->wali_id,
                    'tanggal_bayar' => $request->tanggal_bayar,
                    'jumlah_dibayar' => $jumlahUntukItem,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'bukti_bayar' => $buktiPath,
                    'status_konfirmasi' => $isWali ? 'Belum Dikonfirmasi' : $request->status_konfirmasi,
                    'bank_sekolah_id' => $request->bank_sekolah_id ?? null,
                    'user_id' => auth()->id(),
                ]);
                $pembayaranIds[] = $pembayaran->id;
            }

            // Commit transaksi jika semua berhasil
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil disimpan',
                'data' => [
                    'pembayaran_ids' => $pembayaranIds,
                    'details' => $tagihanDetails,
                    'sisa_tagihan' => $totalRemaining
                ]
            ]);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollback();
            // Hapus file bukti yang sudah diupload jika ada error
            if (isset($buktiPath)) {
                Storage::disk('public')->delete($buktiPath);
            }
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Mengkonfirmasi pembayaran yang dilakukan oleh wali
     * Hanya operator yang bisa mengkonfirmasi pembayaran
     */
    public function confirm($id)
    {
        try {
            // Cek apakah user adalah operator
            if (auth()->user()->akses !== 'operator') {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengkonfirmasi pembayaran');
            }
            
            // Log untuk debugging
            \Log::info('Konfirmasi pembayaran ID: ' . $id . ' oleh user: ' . auth()->id());
            
            $pembayaran = Pembayaran::findOrFail($id);
            
            // Update status konfirmasi saja dulu
            $pembayaran->status_konfirmasi = 'Sudah Dikonfirmasi';
            $pembayaran->user_id = auth()->id();
            $pembayaran->save();
            
            \Log::info('Pembayaran berhasil dikonfirmasi');
            
            return redirect()->back()->with('success', 'Pembayaran berhasil dikonfirmasi');
        } catch (\Exception $e) {
            \Log::error('Gagal mengkonfirmasi pembayaran ID ' . $id . ': ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Gagal mengkonfirmasi pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan daftar pembayaran untuk operator/admin
     * Dilengkapi dengan fitur filter dan pencarian
     */
    public function index(Request $request)
    {
        $query = Pembayaran::with(['tagihan.siswa', 'tagihan_detail', 'wali', 'user'])
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status konfirmasi
        if ($request->has('status_konfirmasi') && $request->status_konfirmasi != '') {
            $query->where('status_konfirmasi', $request->status_konfirmasi);
        }

        // Filter berdasarkan metode pembayaran
        if ($request->has('metode_pembayaran') && $request->metode_pembayaran != '') {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }

        // Filter berdasarkan tanggal dari
        if ($request->has('tanggal_dari') && $request->tanggal_dari != '') {
            $query->whereDate('tanggal_bayar', '>=', $request->tanggal_dari);
        }

        // Filter berdasarkan tanggal sampai
        if ($request->has('tanggal_sampai') && $request->tanggal_sampai != '') {
            $query->whereDate('tanggal_bayar', '<=', $request->tanggal_sampai);
        }

        // Filter pencarian berdasarkan nama atau NISN siswa
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('tagihan.siswa', function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('nisn', 'like', '%' . $search . '%');
            });
        }

        $pembayaran = $query->paginate(15)->withQueryString();

        return view('operator.pembayaran_index', [
            'pembayaran' => $pembayaran,
            'title' => 'Data Pembayaran',
            'search' => $request->search,
            'status_konfirmasi' => $request->status_konfirmasi,
            'metode_pembayaran' => $request->metode_pembayaran,
            'tanggal_dari' => $request->tanggal_dari,
            'tanggal_sampai' => $request->tanggal_sampai
        ]);
    }

    /**
     * Menampilkan daftar pembayaran untuk wali
     * Wali hanya bisa melihat pembayaran Bank Transfer dari siswa yang dia kelola
     */
    public function indexWali(Request $request)
    {
        // Ambil siswa yang dimiliki wali yang sedang login
        $siswaIds = Auth::user()->siswa->pluck('id');
        
        $query = Pembayaran::with(['tagihan.siswa', 'tagihan_detail'])
            ->whereHas('tagihan', function($q) use ($siswaIds) {
                $q->whereIn('siswa_id', $siswaIds);
            })
            ->where('metode_pembayaran', 'Bank Transfer') // Wali hanya bisa melihat Bank Transfer
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status konfirmasi
        if ($request->has('status_konfirmasi') && $request->status_konfirmasi != '') {
            $query->where('status_konfirmasi', $request->status_konfirmasi);
        }

        // Filter berdasarkan metode pembayaran (hanya Bank Transfer)
        if ($request->has('metode_pembayaran') && $request->metode_pembayaran != '') {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }

        // Filter berdasarkan tanggal dari
        if ($request->has('tanggal_dari') && $request->tanggal_dari != '') {
            $query->whereDate('tanggal_bayar', '>=', $request->tanggal_dari);
        }

        // Filter berdasarkan tanggal sampai
        if ($request->has('tanggal_sampai') && $request->tanggal_sampai != '') {
            $query->whereDate('tanggal_bayar', '<=', $request->tanggal_sampai);
        }

        // Filter pencarian berdasarkan nama atau NISN siswa
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('tagihan.siswa', function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('nisn', 'like', '%' . $search . '%');
            });
        }

        $pembayaran = $query->paginate(15)->withQueryString();

        return view('wali.pembayaran_index', [
            'pembayaran' => $pembayaran,
            'title' => 'Data Pembayaran',
            'search' => $request->search,
            'status_konfirmasi' => $request->status_konfirmasi,
            'metode_pembayaran' => $request->metode_pembayaran,
            'tanggal_dari' => $request->tanggal_dari,
            'tanggal_sampai' => $request->tanggal_sampai
        ]);
    }
}