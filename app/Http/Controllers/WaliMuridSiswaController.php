<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Siswa;
use App\Models\Jurusan;

class WaliMuridSiswaController extends Controller
{
    public function index(Request $request)
    {
        $data['title'] = "Data Siswa";
        
        $query = Auth::user()->siswa()->with(['wali', 'jurusan']);
        
        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('kelas', 'like', "%{$search}%")
                  ->orWhere('angkatan', 'like', "%{$search}%")
                  ->orWhereHas('jurusan', function($jurusanQuery) use ($search) {
                      $jurusanQuery->where('nama', 'like', "%{$search}%");
                  });
            });
        }
        
        $data['models'] = $query->get();
        $data['search'] = $request->search;
        
        return view('wali.siswa_index', $data);
    }

    public function show($id)
    {
        // Cek apakah siswa ini adalah anak dari wali yang sedang login
        $siswa = Auth::user()->siswa()->with(['wali', 'jurusan', 'tagihan.tagihan_details', 'tagihan.pembayaran'])->findOrFail($id);
        
        return view('wali.siswa_show', [
            'title' => 'Detail Siswa',
            'siswa' => $siswa,
            'total_tagihan' => $siswa->total_tagihan,
            'total_pembayaran' => $siswa->total_pembayaran
        ]);
    }

    public function edit($id)
    {
        // Cek apakah siswa ini adalah anak dari wali yang sedang login
        $siswa = Auth::user()->siswa()->with(['wali', 'jurusan'])->findOrFail($id);
        $jurusan = Jurusan::all();
        
        return view('wali.siswa_form', [
            'title' => 'Edit Data Siswa',
            'siswa' => $siswa,
            'jurusan' => $jurusan,
            'method' => 'PUT',
            'action' => route('wali.siswa.update', $id)
        ]);
    }

    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            'nisn' => 'required|string|max:20',
            'kelas' => 'required|string|max:20',
            'angkatan' => 'required|string|max:4',
            'jurusan_id' => 'required|exists:jurusans,id',
        ]);

        // Cek apakah siswa ini adalah anak dari wali yang sedang login
        $siswa = Auth::user()->siswa()->findOrFail($id);
        
        // Update data siswa
        $siswa->update([
            'nama' => $request->nama,
            'nisn' => $request->nisn,
            'kelas' => $request->kelas,
            'angkatan' => $request->angkatan,
            'jurusan_id' => $request->jurusan_id,
        ]);

        return redirect()->route('wali.siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui');
    }
}