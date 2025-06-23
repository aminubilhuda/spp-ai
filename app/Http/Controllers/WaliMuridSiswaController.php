<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
}