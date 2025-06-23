<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class WaliMuridTagihanController extends Controller
{
    public function index(Request $request)
    {
        $data['title'] = "Data Tagihan";
        $siswa = Auth::user()->siswa->pluck('id');
        $data['tagihan'] = Tagihan::with(['siswa', 'tagihan_details'])->whereIn('siswa_id', $siswa)->get();
        return view('wali.tagihan_index', $data);
    }

    public function show($id)
    {
        $siswa = Auth::user()->siswa->pluck('id');
        $tagihan = Tagihan::with(['siswa', 'tagihan_details'])->whereIn('siswa_id', $siswa)->findOrFail($id);
        
        $data['title'] = "Detail Tagihan";
        $data['tagihan'] = $tagihan;
        
        return view('wali.tagihan_show', $data);
    }
}