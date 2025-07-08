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
            'detail_ids' => 'required|array|min:1',
            'detail_ids.*' => 'exists:tagihan_details,id',
            'jumlah_dibayar' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:Bank Transfer,Cash',
            'tanggal_bayar' => 'required|date',
            'status_konfirmasi' => 'required|in:Belum Dikonfirmasi,Sudah Dikonfirmasi',
        ];

        // Jika wali, hanya boleh Bank Transfer dan bukti wajib
        if ($isWali) {
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
        }

        $request->validate($validationRules);

        // Mulai transaksi database untuk memastikan konsistensi data
        DB::beginTransaction();
        try {
            // Ambil semua detail tagihan yang dipilih
            $tagihanDetails = TagihanDetail::whereIn('id', $request->detail_ids)->get();
            
            // Validasi bahwa semua detail tagihan ditemukan
            if ($tagihanDetails->count() !== count($request->detail_ids)) {
                throw new \Exception('Beberapa item tagihan tidak valid');
            }

            // Kelompokkan detail berdasarkan tagihan_id
            $detailsByTagihan = $tagihanDetails->groupBy('tagihan_id');
            
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

            // Buat record pembayaran untuk setiap tagihan
            $pembayaranIds = [];
            
            foreach ($detailsByTagihan as $tagihanId => $details) {
                $tagihan = Tagihan::findOrFail($tagihanId);
                
                // Hitung total sisa tagihan untuk tagihan ini
                $totalSisaTagihan = 0;
                foreach ($details as $detail) {
                    $totalDibayarDetail = $detail->pembayaran()
                        ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                        ->sum('jumlah_dibayar');
                    $sisaBayar = $detail->jumlah_biaya - $totalDibayarDetail;
                    $sisaBayar = max(0, $sisaBayar);
                    $totalSisaTagihan += $sisaBayar;
                }

                // Hitung proporsi pembayaran untuk tagihan ini
                $proporsiPembayaran = ($totalSisaTagihan / $totalRemaining) * $request->jumlah_dibayar;

                // Buat pembayaran untuk setiap detail dalam tagihan ini
                foreach ($details as $detail) {
                    $totalDibayarDetail = $detail->pembayaran()
                        ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                        ->sum('jumlah_dibayar');
                    $sisaBayar = $detail->jumlah_biaya - $totalDibayarDetail;
                    $sisaBayar = max(0, $sisaBayar);
                    
                    // Hitung jumlah pembayaran untuk detail ini secara proporsional
                    if ($totalSisaTagihan > 0) {
                        $proporsiDetail = ($sisaBayar / $totalSisaTagihan) * $proporsiPembayaran;
                    } else {
                        $proporsiDetail = 0;
                    }

                    // Buat record pembayaran
                    $pembayaran = Pembayaran::create([
                        'tagihan_id' => $tagihanId,
                        'tagihan_detail_id' => $detail->id,
                        'wali_id' => $tagihan->siswa->wali_id,
                        'tanggal_bayar' => $request->tanggal_bayar,
                        'jumlah_dibayar' => $proporsiDetail,
                        'metode_pembayaran' => $request->metode_pembayaran,
                        'bukti_bayar' => $buktiPath,
                        'status_konfirmasi' => $isWali ? 'Belum Dikonfirmasi' : $request->status_konfirmasi,
                        'bank_sekolah_id' => $request->bank_sekolah_id ?? null,
                        'user_id' => auth()->id(),
                        'no_rekening_pengirim' => $request->no_rekening_pengirim,
                        'bank_pengirim' => $request->bank_pengirim,
                    ]);
                    
                    // Set status awal menggunakan package Spatie
                    $statusReason = $isWali ? 'Pembayaran dibuat oleh wali, menunggu konfirmasi' : 'Pembayaran dibuat oleh operator';
                    $pembayaran->setStatus('pending', $statusReason);
                    
                    // Update pembayaran_id di tagihan_details
                    $detail->update(['pembayaran_id' => $pembayaran->id]);
                    
                    $pembayaranIds[] = $pembayaran->id;
                }
            }

            // Commit transaksi jika semua berhasil
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil disimpan',
                'data' => [
                    'pembayaran_ids' => $pembayaranIds
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
            
            // Update status konfirmasi lama
            $pembayaran->status_konfirmasi = 'Sudah Dikonfirmasi';
            $pembayaran->user_id = auth()->id();
            $pembayaran->save();
            
            // Set status baru menggunakan package Spatie
            $pembayaran->setStatus('confirmed', 'Pembayaran dikonfirmasi oleh operator: ' . auth()->user()->name);
            
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

    /**
     * Menghapus pembayaran (hanya untuk operator/admin)
     * Dengan validasi dan audit trail
     */
    public function destroy($id)
    {
        try {
            // Cek apakah user adalah operator/admin
            if (!in_array(auth()->user()->akses, ['admin', 'operator'])) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus pembayaran');
            }
            
            $pembayaran = Pembayaran::with(['tagihan.siswa', 'tagihan_detail'])->findOrFail($id);
            
            // Validasi apakah pembayaran bisa dihapus
            $canDelete = $this->canDeletePembayaran($pembayaran);
            
            if (!$canDelete['can_delete']) {
                return redirect()->back()->with('error', $canDelete['message']);
            }
            
            \DB::beginTransaction();
            
            // Log audit trail sebelum dihapus
            \Log::info('Pembayaran akan dihapus', [
                'pembayaran_id' => $pembayaran->id,
                'siswa' => $pembayaran->tagihan->siswa->nama,
                'jumlah' => $pembayaran->jumlah_dibayar,
                'status' => $pembayaran->status_konfirmasi,
                'deleted_by' => auth()->user()->name,
                'deleted_at' => now()
            ]);
            
            // Hapus file bukti pembayaran jika ada
            if ($pembayaran->bukti_bayar) {
                Storage::disk('public')->delete($pembayaran->bukti_bayar);
                Storage::disk('public')->delete(str_replace('bukti_pembayaran/', '', $pembayaran->bukti_bayar));
            }
            
            // Update tagihan_detail jika ada pembayaran_id
            if ($pembayaran->tagihan_detail && $pembayaran->tagihan_detail->pembayaran_id == $pembayaran->id) {
                $pembayaran->tagihan_detail->update(['pembayaran_id' => null]);
            }
            
            // Hapus pembayaran
            $pembayaran->delete();
            
            // Update status tagihan detail
            if ($pembayaran->tagihan_detail) {
                $this->updateTagihanDetailStatusAfterDelete($pembayaran->tagihan_detail);
            }
            
            \DB::commit();
            
            return redirect()->back()->with('success', 'Pembayaran berhasil dihapus');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Gagal menghapus pembayaran: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Gagal menghapus pembayaran: ' . $e->getMessage());
        }
    }
    
    /**
     * Validasi apakah pembayaran bisa dihapus
     */
    private function canDeletePembayaran(Pembayaran $pembayaran)
    {
        // Jika pembayaran sudah dikonfirmasi, perlu approval khusus
        if ($pembayaran->status_konfirmasi === 'Sudah Dikonfirmasi') {
            // Cek apakah user adalah admin atau operator
            if (!in_array(auth()->user()->akses, ['admin', 'operator'])) {
                return [
                    'can_delete' => false,
                    'message' => 'Hanya admin dan operator yang dapat menghapus pembayaran yang sudah dikonfirmasi'
                ];
            }
            
            // Cek apakah pembayaran sudah lebih dari 7 hari
            if ($pembayaran->created_at->diffInDays(now()) > 7) {
                return [
                    'can_delete' => false,
                    'message' => 'Pembayaran yang sudah dikonfirmasi lebih dari 7 hari tidak dapat dihapus'
                ];
            }
        }
        
        // Jika pembayaran belum dikonfirmasi, operator dan admin bisa hapus
        if ($pembayaran->status_konfirmasi === 'Belum Dikonfirmasi') {
            // Cek apakah user adalah admin atau operator
            if (!in_array(auth()->user()->akses, ['admin', 'operator'])) {
                return [
                    'can_delete' => false,
                    'message' => 'Hanya admin dan operator yang dapat menghapus pembayaran'
                ];
            }
            
            // Cek apakah pembayaran sudah lebih dari 30 hari
            if ($pembayaran->created_at->diffInDays(now()) > 30) {
                return [
                    'can_delete' => false,
                    'message' => 'Pembayaran yang belum dikonfirmasi lebih dari 30 hari tidak dapat dihapus'
                ];
            }
        }
        
        return [
            'can_delete' => true,
            'message' => 'Pembayaran dapat dihapus'
        ];
    }
    
    /**
     * Update status tagihan detail setelah pembayaran dihapus
     */
    private function updateTagihanDetailStatusAfterDelete($tagihanDetail)
    {
        // Hitung ulang total pembayaran yang sudah dikonfirmasi
        $totalPembayaran = $tagihanDetail->pembayaran()
            ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
            ->sum('jumlah_dibayar');

        // Update status berdasarkan total pembayaran
        if ($totalPembayaran >= $tagihanDetail->jumlah_biaya) {
            $tagihanDetail->status = 'lunas';
            if (!$tagihanDetail->tanggal_lunas) {
                $tagihanDetail->tanggal_lunas = now();
            }
        } elseif ($totalPembayaran > 0) {
            $tagihanDetail->status = 'angsur';
            $tagihanDetail->tanggal_lunas = null;
        } else {
            $tagihanDetail->status = 'belum_lunas';
            $tagihanDetail->tanggal_lunas = null;
        }

        $tagihanDetail->save();
    }
}