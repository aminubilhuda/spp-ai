<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class TahunPelajaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tahunPelajarans = TahunPelajaran::orderByDesc('is_aktif')->orderBy('nama')->get();
        return view('operator.tahun_pelajaran_index', compact('tahunPelajarans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('operator.tahun_pelajaran_form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|unique:tahun_pelajarans,nama',
        ]);
        TahunPelajaran::create([
            'nama' => $request->nama,
            'is_aktif' => 0
        ]);
        return redirect()->route('tahun-pelajaran.index')->with('success', 'Tahun pelajaran berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TahunPelajaran $tahunPelajaran)
    {
        return view('operator.tahun_pelajaran_form', compact('tahunPelajaran'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TahunPelajaran $tahunPelajaran)
    {
        $request->validate([
            'nama' => 'required|unique:tahun_pelajarans,nama,' . $tahunPelajaran->id,
        ]);
        $tahunPelajaran->update([
            'nama' => $request->nama,
        ]);
        return redirect()->route('tahun-pelajaran.index')->with('success', 'Tahun pelajaran berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TahunPelajaran $tahunPelajaran)
    {
        $tahunPelajaran->delete();
        return redirect()->route('tahun-pelajaran.index')->with('success', 'Tahun pelajaran berhasil dihapus');
    }

    public function setAktif($id)
    {
        // Nonaktifkan semua
        TahunPelajaran::query()->update(['is_aktif' => 0]);
        // Aktifkan yang dipilih
        TahunPelajaran::where('id', $id)->update(['is_aktif' => 1]);
        return redirect()->route('tahun-pelajaran.index')->with('success', 'Tahun pelajaran aktif berhasil diubah');
    }
}
