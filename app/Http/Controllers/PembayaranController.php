<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TagihanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'tagihan_id' => 'required|exists:tagihans,id',
            'detail_id' => 'required|exists:tagihan_details,id',
            'siswa_id' => 'required|exists:siswas,id',
            'jumlah_dibayar' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:Bank Transfer,Cash',
            'tanggal_bayar' => 'required|date',
            'status_konfirmasi' => 'required|in:Belum Dikonfirmasi,Sudah Dikonfirmasi',
            'bukti_bayar' => 'required_if:metode_pembayaran,Bank Transfer|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Get the specific tagihan detail
            $detail = TagihanDetail::with(['tagihan.siswa'])->findOrFail($request->detail_id);
            $tagihan = $detail->tagihan;
            
            if (!$tagihan->siswa || !$tagihan->siswa->wali_id) {
                throw new \Exception('Siswa belum memiliki wali yang terdaftar');
            }
            
            // Calculate remaining amount for this specific detail
            $totalDibayar = $detail->pembayaran->sum('jumlah_dibayar');
            $sisaBayar = $detail->jumlah_biaya - $totalDibayar;

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

            // Create payment record for this detail
            $pembayaran = Pembayaran::create([
                'tagihan_id' => $request->tagihan_id,
                'tagihan_detail_id' => $request->detail_id,
                'wali_id' => $tagihan->siswa->wali_id,
                'tanggal_bayar' => $request->tanggal_bayar,
                'jumlah_dibayar' => $request->jumlah_dibayar,
                'metode_pembayaran' => $request->metode_pembayaran,
                'bukti_bayar' => $buktiPath,
                'status_konfirmasi' => $request->status_konfirmasi,
                'user_id' => auth()->id(),
            ]);

            // Update detail status
            $totalPaidForDetail = $detail->pembayaran()->sum('jumlah_dibayar');
            if ($totalPaidForDetail >= $detail->jumlah_biaya) {
                $detail->status = 'lunas';
            } elseif ($totalPaidForDetail > 0) {
                $detail->status = 'angsur';
            } else {
                $detail->status = 'belum_lunas';
            }
            $detail->save();

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil disimpan',
                'data' => [
                    'pembayaran' => $pembayaran,
                    'detail' => $detail,
                    'sisa_tagihan' => max(0, $detail->jumlah_biaya - ($totalPaidForDetail))
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