<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\TagihanDetail;
use Illuminate\Http\Request;

class TagihanRekapController extends Controller
{
    public function show($siswa_id)
    {
        $siswa = Siswa::with('jurusan')->findOrFail($siswa_id);
        
        $tagihan_details = TagihanDetail::with(['tagihan.siswa', 'pembayaran'])
            ->whereHas('tagihan', function($query) use ($siswa_id) {
                $query->where('siswa_id', $siswa_id);
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('operator.tagihan_rekap', compact('siswa', 'tagihan_details'));
    }
}
