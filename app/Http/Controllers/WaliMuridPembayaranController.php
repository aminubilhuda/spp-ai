<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TagihanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PembayaranNotification;

class WaliMuridPembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil siswa yang dimiliki wali yang sedang login
        $siswaIds = Auth::user()->siswa->pluck('id');
        
        $query = Pembayaran::with(['tagihan.siswa', 'tagihan_detail', 'bank_sekolah'])
            ->whereHas('tagihan', function($q) use ($siswaIds) {
                $q->whereIn('siswa_id', $siswaIds);
            })
            ->where('metode_pembayaran', 'Bank Transfer') // Wali hanya bisa melihat Bank Transfer
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status konfirmasi
        if ($request->has('status_konfirmasi') && $request->status_konfirmasi != '') {
            $query->where('status_konfirmasi', $request->status_konfirmasi);
        }

        // Filter berdasarkan bulan/tahun
        if ($request->has('bulan_tahun') && $request->bulan_tahun != '') {
            $bulanTahun = $request->bulan_tahun;
            $query->whereYear('tanggal_bayar', substr($bulanTahun, 0, 4))
                  ->whereMonth('tanggal_bayar', substr($bulanTahun, 5, 2));
        }

        // Filter pencarian
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
            'bulan_tahun' => $request->bulan_tahun
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate request untuk wali murid
        $request->validate([
            'tagihan_id' => 'required|exists:tagihans,id',
            'detail_ids' => 'required|array|min:1',
            'detail_ids.*' => 'exists:tagihan_details,id',
            'jumlah_dibayar' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:Bank Transfer',
            'bukti_bayar' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'tanggal_bayar' => 'required|date',
            'bank_sekolah_id' => 'required|exists:bank_sekolahs,id',
            'no_rekening_pengirim' => 'required|string|max:50',
            'bank_pengirim' => 'required|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Get tagihan and validate
            $tagihan = Tagihan::findOrFail($request->tagihan_id);
            
            // Validasi bahwa wali yang login memiliki akses ke siswa ini
            $siswaIds = Auth::user()->siswa->pluck('id');
            if (!in_array($tagihan->siswa_id, $siswaIds->toArray())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke tagihan ini'
                ], 403);
            }
            
            // Validate that all detail_ids belong to the tagihan
            $tagihanDetails = TagihanDetail::whereIn('id', $request->detail_ids)
                ->where('tagihan_id', $tagihan->id)
                ->get();
                
            if ($tagihanDetails->count() !== count($request->detail_ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Beberapa item tagihan tidak valid'
                ], 400);
            }

            // Calculate total remaining amount for selected details
            $totalRemaining = 0;
            foreach ($tagihanDetails as $detail) {
                $totalDibayar = $detail->pembayaran()
                    ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                    ->sum('jumlah_dibayar');
                $sisaBayar = $detail->jumlah_biaya - $totalDibayar;
                $totalRemaining += $sisaBayar;
            }

            // Validate payment amount
            if ($request->jumlah_dibayar > $totalRemaining) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran melebihi sisa tagihan yang harus dibayar'
                ], 400);
            }

            // Handle file upload
            $file = $request->file('bukti_bayar');
            $buktiPath = $file->store('bukti_pembayaran', 'public');

            // Create payment records for each selected detail
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
                    // Pembayaran parsial: bagi secara proporsional
                    $proporsi = $sisaBayar / $totalSisaTagihan;
                    $jumlahUntukItem = $jumlahDibayar * $proporsi;
                } else {
                    // Pembayaran penuh atau sesuai sisa tagihan
                    $jumlahUntukItem = $sisaBayar;
                }

                $pembayaran = Pembayaran::create([
                    'tagihan_id'        => $request->tagihan_id,
                    'tagihan_detail_id' => $detail->id,
                    'wali_id'           => $tagihan->siswa->wali_id,
                    'tanggal_bayar'     => $request->tanggal_bayar,
                    'jumlah_dibayar'    => $jumlahUntukItem,
                    'metode_pembayaran' => 'Bank Transfer',
                    'bukti_bayar'       => $buktiPath,
                    'status_konfirmasi' => 'Belum Dikonfirmasi', // Wali selalu belum dikonfirmasi
                    'bank_sekolah_id'   => $request->bank_sekolah_id,
                    'no_rekening_pengirim' => $request->no_rekening_pengirim,
                    'bank_pengirim'     => $request->bank_pengirim,
                    'user_id'           => auth()->id(),
                ]);
                $pembayaranIds[] = $pembayaran->id;
            }

            DB::commit();
            
            // Kirim notifikasi ke operator setelah semua pembayaran berhasil dibuat
            $this->sendNotificationToOperators($pembayaranIds[0]); // Ambil ID pembayaran pertama
            
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil disimpan dan menunggu konfirmasi dari operator',
                'data' => [
                    'pembayaran_ids'    => $pembayaranIds,
                    'details'           => $tagihanDetails,
                    'sisa_tagihan'      => $totalRemaining
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
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
     * Display the specified resource.
     */
    public function show($id)
    {
        // Ambil siswa yang dimiliki wali yang sedang login
        $siswaIds = Auth::user()->siswa->pluck('id');
        
        $pembayaran = Pembayaran::with(['tagihan.siswa', 'tagihan_detail', 'bank_sekolah', 'user'])
            ->whereHas('tagihan', function($q) use ($siswaIds) {
                $q->whereIn('siswa_id', $siswaIds);
            })
            ->findOrFail($id);

        return view('wali.pembayaran_show', [
            'pembayaran' => $pembayaran,
            'title' => 'Detail Pembayaran'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Wali tidak bisa mengedit pembayaran yang sudah dibuat
        return redirect()->back()->with('error', 'Wali murid tidak dapat mengedit pembayaran');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Wali tidak bisa mengupdate pembayaran yang sudah dibuat
        return redirect()->back()->with('error', 'Wali murid tidak dapat mengubah pembayaran');
    }

    /**
     * Remove the specified resource from storage.
     * Wali hanya bisa membatalkan pembayaran yang belum dikonfirmasi
     */
    public function destroy($id)
    {
        try {
            // Ambil siswa yang dimiliki wali yang sedang login
            $siswaIds = Auth::user()->siswa->pluck('id');
            
            $pembayaran = Pembayaran::with(['tagihan.siswa', 'tagihan_detail'])
                ->whereHas('tagihan', function($q) use ($siswaIds) {
                    $q->whereIn('siswa_id', $siswaIds);
                })
                ->where('id', $id)
                ->first();

            if (!$pembayaran) {
                return redirect()->back()->with('error', 'Pembayaran tidak ditemukan atau Anda tidak memiliki akses');
            }

            // Validasi bahwa pembayaran belum dikonfirmasi
            if ($pembayaran->status_konfirmasi === 'Sudah Dikonfirmasi') {
                return redirect()->back()->with('error', 'Pembayaran yang sudah dikonfirmasi tidak dapat dibatalkan');
            }

            // Validasi bahwa pembayaran dibuat oleh wali yang sedang login
            if ($pembayaran->wali_id !== Auth::id()) {
                return redirect()->back()->with('error', 'Anda hanya dapat membatalkan pembayaran yang Anda buat');
            }

            // Validasi waktu pembayaran (maksimal 24 jam setelah dibuat)
            $createdTime = $pembayaran->created_at;
            $currentTime = now();
            $hoursDiff = $currentTime->diffInHours($createdTime);

            if ($hoursDiff > 24) {
                return redirect()->back()->with('error', 'Pembayaran hanya dapat dibatalkan dalam waktu 24 jam setelah dibuat');
            }

            DB::beginTransaction();
            try {
                // Hapus file bukti pembayaran jika ada
                if ($pembayaran->bukti_bayar) {
                    Storage::disk('public')->delete($pembayaran->bukti_bayar);
                }

                // Hapus pembayaran
                $pembayaran->delete();

                DB::commit();

                // Log aktivitas pembatalan
                \Log::info('Pembayaran dibatalkan oleh wali', [
                    'pembayaran_id' => $id,
                    'wali_id' => Auth::id(),
                    'wali_name' => Auth::user()->name,
                    'siswa_name' => $pembayaran->tagihan->siswa->nama,
                    'jumlah_dibayar' => $pembayaran->jumlah_dibayar,
                    'created_at' => $createdTime,
                    'cancelled_at' => $currentTime,
                    'hours_diff' => $hoursDiff
                ]);

                return redirect()->route('wali.pembayaran.index')
                    ->with('success', 'Pembayaran berhasil dibatalkan');

            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Error saat membatalkan pembayaran', [
                    'pembayaran_id' => $id,
                    'error' => $e->getMessage(),
                    'wali_id' => Auth::id()
                ]);

                return redirect()->back()->with('error', 'Gagal membatalkan pembayaran: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            \Log::error('Error dalam destroy pembayaran', [
                'pembayaran_id' => $id,
                'error' => $e->getMessage(),
                'wali_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat membatalkan pembayaran');
        }
    }

    /**
     * Get tagihan details for payment modal
     */
    public function getTagihanDetails($tagihanId)
    {
        try {
            // Ambil siswa yang dimiliki wali yang sedang login
            $siswaIds = Auth::user()->siswa->pluck('id');
            
            $tagihan = Tagihan::with(['siswa', 'tagihan_details.biaya'])
                ->whereIn('siswa_id', $siswaIds)
                ->findOrFail($tagihanId);

            $details = [];
            $totalTagihan = 0;

            foreach ($tagihan->tagihan_details as $detail) {
                // Hitung total pembayaran yang sudah dikonfirmasi
                $totalDibayar = $detail->pembayaran()
                    ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                    ->sum('jumlah_dibayar');
                $sisaBayar = $detail->jumlah_biaya - $totalDibayar;

                if ($sisaBayar > 0) {
                    $details[] = [
                        'id' => $detail->id,
                        'nama_biaya' => $detail->biaya->nama_biaya,
                        'jumlah_biaya' => $detail->jumlah_biaya,
                        'sisa_bayar' => $sisaBayar,
                        'status' => $detail->status
                    ];
                    $totalTagihan += $sisaBayar;
                }
            }

            return response()->json([
                'success' => true,
                'tagihan' => $tagihan,
                'details' => $details,
                'total_tagihan' => $totalTagihan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan saat mengambil data tagihan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification to all operators
     */
    private function sendNotificationToOperators($pembayaranId): void
    {
        try {
            $pembayaran = Pembayaran::with(['tagihan.siswa.wali', 'tagihan_detail', 'user'])->find($pembayaranId);
            
            if ($pembayaran) {
                // Ambil semua user dengan akses operator
                $operators = \App\Models\User::where('akses', 'operator')->get();
                
                // Kirim notifikasi ke semua operator
                Notification::send($operators, new PembayaranNotification($pembayaran));
            }
        } catch (\Exception $e) {
            // Log error jika gagal mengirim notifikasi
            \Log::error('Gagal mengirim notifikasi pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Batalkan pembayaran via AJAX
     */
    public function cancelPayment($id)
    {
        try {
            \Log::info('Cancel payment request received', [
                'pembayaran_id' => $id,
                'wali_id' => Auth::id(),
                'request_data' => request()->all()
            ]);

            // Ambil siswa yang dimiliki wali yang sedang login
            $siswaIds = Auth::user()->siswa->pluck('id');
            
            $pembayaran = Pembayaran::with(['tagihan.siswa', 'tagihan_detail'])
                ->whereHas('tagihan', function($q) use ($siswaIds) {
                    $q->whereIn('siswa_id', $siswaIds);
                })
                ->where('id', $id)
                ->first();

            if (!$pembayaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran tidak ditemukan atau Anda tidak memiliki akses'
                ], 404);
            }

            // Validasi bahwa pembayaran belum dikonfirmasi
            if ($pembayaran->status_konfirmasi === 'Sudah Dikonfirmasi') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran yang sudah dikonfirmasi tidak dapat dibatalkan'
                ], 400);
            }

            // Validasi bahwa pembayaran dibuat oleh wali yang sedang login
            if ($pembayaran->wali_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat membatalkan pembayaran yang Anda buat'
                ], 403);
            }

            // Validasi waktu pembayaran (maksimal 24 jam setelah dibuat)
            $createdTime = $pembayaran->created_at;
            $currentTime = now();
            $hoursDiff = $currentTime->diffInHours($createdTime);

            if ($hoursDiff > 24) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran hanya dapat dibatalkan dalam waktu 24 jam setelah dibuat'
                ], 400);
            }

            DB::beginTransaction();
            try {
                // Hapus file bukti pembayaran jika ada
                if ($pembayaran->bukti_bayar) {
                    Storage::disk('public')->delete($pembayaran->bukti_bayar);
                }

                // Simpan data pembayaran sebelum dihapus untuk logging
                $pembayaranData = [
                    'id' => $pembayaran->id,
                    'jumlah_dibayar' => $pembayaran->jumlah_dibayar,
                    'siswa_name' => $pembayaran->tagihan->siswa->nama,
                    'created_at' => $pembayaran->created_at
                ];

                // Hapus pembayaran
                $pembayaran->delete();

                DB::commit();

                // Log aktivitas pembatalan
                \Log::info('Pembayaran dibatalkan oleh wali via AJAX', [
                    'pembayaran_id' => $id,
                    'wali_id' => Auth::id(),
                    'wali_name' => Auth::user()->name,
                    'pembayaran_data' => $pembayaranData,
                    'cancelled_at' => $currentTime,
                    'hours_diff' => $hoursDiff
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran berhasil dibatalkan',
                    'data' => [
                        'pembayaran_id' => $id,
                        'cancelled_at' => $currentTime->format('Y-m-d H:i:s')
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Error saat membatalkan pembayaran via AJAX', [
                    'pembayaran_id' => $id,
                    'error' => $e->getMessage(),
                    'wali_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membatalkan pembayaran: ' . $e->getMessage()
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Error dalam cancelPayment', [
                'pembayaran_id' => $id,
                'error' => $e->getMessage(),
                'wali_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membatalkan pembayaran'
            ], 500);
        }
    }
} 