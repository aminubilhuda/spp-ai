<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{    public function store(Request $request)
    {
        $request->validate([
            'tagihan_id' => 'required|exists:tagihans,id',
            'siswa_id' => 'required|exists:siswas,id',
            'jumlah_dibayar' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:Bank Transfer,Cash',
            'tanggal_bayar' => 'required|date',
            'status_konfirmasi' => 'required|in:Belum Dikonfirmasi,Sudah Dikonfirmasi',
            'bukti_bayar' => 'required_if:metode_pembayaran,Bank Transfer|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        DB::beginTransaction();        try {
            // Get tagihan with needed relations
            $tagihan = Tagihan::with(['tagihan_details', 'pembayaran', 'siswa'])->findOrFail($request->tagihan_id);
            
            if (!$tagihan->siswa || !$tagihan->siswa->wali_id) {
                throw new \Exception('Siswa belum memiliki wali yang terdaftar');
            }
            
            // Calculate total tagihan and remaining amount for all details
            $totalTagihan = $tagihan->tagihan_details->sum('jumlah_biaya');
            $totalDibayar = $tagihan->pembayaran->sum('jumlah_dibayar');
            $sisaBayar = $totalTagihan - $totalDibayar;

            // Validate payment amount
            if ($request->jumlah_dibayar > $sisaBayar) {
                throw new \Exception('Jumlah pembayaran melebihi sisa tagihan yang harus dibayar');
            }
            
            // Handle file upload if exists
            $buktiPath = null;
            if ($request->hasFile('bukti_bayar')) {
                $file = $request->file('bukti_bayar');
                $extension = $file->getClientOriginalExtension();
                $fileName = 'bukti_' . $tagihan->siswa->id . '_' . time() . '.' . $extension;
                $buktiPath = $file->storeAs('bukti-pembayaran', $fileName, 'public');
            }

            // Create payment record with validated wali_id
            $wali_id = $tagihan->siswa->wali_id;
            if (!$wali_id) {
                throw new \Exception('Data wali tidak ditemukan');
            }

            // Distribute payment across unpaid details proportionally
            $remainingPayment = $request->jumlah_dibayar;
            $unfulfilledDetails = $tagihan->tagihan_details->filter(function($detail) {
                $paidAmount = $detail->pembayaran()->sum('jumlah_dibayar');
                return $paidAmount < $detail->jumlah_biaya;
            });

            foreach ($unfulfilledDetails as $detail) {
                if ($remainingPayment <= 0) break;

                $detailUnpaid = $detail->jumlah_biaya - $detail->pembayaran()->sum('jumlah_dibayar');
                $paymentForDetail = min($detailUnpaid, $remainingPayment);

                if ($paymentForDetail > 0) {
                    $pembayaran = Pembayaran::create([
                        'tagihan_id' => $request->tagihan_id,
                        'tagihan_detail_id' => $detail->id,
                        'wali_id' => $wali_id,
                        'tanggal_bayar' => $request->tanggal_bayar,
                        'jumlah_dibayar' => $paymentForDetail,
                        'metode_pembayaran' => $request->metode_pembayaran,
                        'bukti_bayar' => $buktiPath,
                        'status_konfirmasi' => $request->status_konfirmasi,
                        'user_id' => auth()->id(),
                    ]);

                    $remainingPayment -= $paymentForDetail;

                    // Update detail status
                    $totalPaidForDetail = $detail->pembayaran()->sum('jumlah_dibayar') + $paymentForDetail;
                    if ($totalPaidForDetail >= $detail->jumlah_biaya) {
                        $detail->status = 'lunas';
                    } elseif ($totalPaidForDetail > 0) {
                        $detail->status = 'angsur';
                    } else {
                        $detail->status = 'belum_lunas';
                    }
                    $detail->save();
                }
            }            // Status now managed at the detail level only
            $tagihan->save();

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil disimpan',
                'data' => [
                    'pembayaran' => $pembayaran ?? null,
                    'tagihan' => $tagihan,
                    'sisa_tagihan' => max(0, $totalTagihan - ($totalDibayar + $request->jumlah_dibayar))
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
}