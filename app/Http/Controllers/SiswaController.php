<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jurusan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Siswa as Model;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreSiswaRequest;
use App\Http\Requests\UpdateSiswaRequest;
use App\Exports\SiswaExport;
use App\Exports\SiswaImportTemplate;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
 
//refactor code
class SiswaController extends Controller
{
    private $viewIndex = 'siswa_index';
    private $viewCreate = 'siswa_form';
    private $viewEdit = 'siswa_form';
    private $viewShow = 'siswa_detail'; // Mengubah template untuk detail siswa
    private $routePrefix = 'siswa';
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Model::query()->with('wali', 'jurusan'); //eager loading -> with();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nisn', 'LIKE', "%{$search}%")
                  ->orWhere('nis', 'LIKE', "%{$search}%")
                  ->orWhere('kelas', 'LIKE', "%{$search}%")
                  ->orWhere('angkatan', 'LIKE', "%{$search}%");
            });
        }
        
        return view ('operator.'.$this->viewIndex, [
            'models' => $query->latest()->paginate(50),
            'routePrefix' => $this->routePrefix,
            'title' => 'Data Siswa',
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'model' => new Model(),
            'method' => 'POST',
            'action' => $this->routePrefix.'.store',
            'title' => 'Form Data Siswa',
            'button' => 'SIMPAN',
        ];
        return view('operator.'.$this->viewCreate, $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSiswaRequest $request)
    {
        // validasi data
        $requestData = $request->validated();
        
        // Upload foto jika ada
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $namaFile = Str::slug($request->nama) . '_' . rand(1000, 9999) . '.' . $foto->getClientOriginalExtension();
            $fotoPath = $foto->storeAs('foto-siswa', $namaFile, 'public');
            $requestData['foto'] = $fotoPath;
        }
        
        // Tambahkan user_id (pengguna yang melakukan input)
        $requestData['user_id'] = auth()->id();
        
        // Buat siswa baru
        Model::create($requestData);
        
        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'Data Siswa berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = [
            'model' => Model::findOrFail($id),
            'title' => 'Detail Siswa',
            'routePrefix' => $this->routePrefix,
        ];
        return view('operator.'.$this->viewShow, $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // edit user
        $data = [
            'model' => Model::findOrFail($id) ,
            'method' => 'PUT',
            'action' => $this->routePrefix.'.update',
            'id' => $id,
            'title' => 'Edit Siswa',
            'button' => 'UPDATE',
        ];
        return view('operator.'.$this->viewEdit, $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSiswaRequest $request, $id)
    {
        // validasi data
        $requestData = $request->validated();
        
        // Upload foto jika ada
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $namaFile = Str::slug($request->nama) . '_' . rand(1000, 9999) . '.' . $foto->getClientOriginalExtension();
            $fotoPath = $foto->storeAs('foto-siswa', $namaFile, 'public');
            $requestData['foto'] = $fotoPath;
        }
        
        // Update siswa
        Model::findOrFail($id)->update($requestData);
        
        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'Data Siswa berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // delete siswa
        $model = Model::findOrFail($id);
        $model->delete();

        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'Siswa berhasil dihapus');
    }

    /**
     * Display the wali murid details for a student.
     */
    public function waliDetail(string $id)
    {
        $siswa = Model::findOrFail($id);
        
        // Redirect if no wali is assigned
        if (!$siswa->wali_id) {
            return redirect()
                ->route($this->routePrefix . '.show', $id)
                ->with('error', 'Siswa belum memiliki data wali murid');
        }
        
        $data = [
            'siswa' => $siswa,
            'model' => $siswa->wali,
            'title' => 'Detail Wali Murid',
            'routePrefix' => $this->routePrefix,
            'siswaList' => \App\Models\Siswa::where('wali_id', $siswa->wali_id)->get(),
        ];
        
        return view('operator.wali_detail', $data);
    }
    
    /**
     * Add a student to a guardian.
     */
    public function tambahKeWali(Request $request)
    {
        // Validate request
        $requestData = $request->validate([
            'wali_id' => 'required|exists:users,id',
            'siswa_id' => 'required|exists:siswas,id',
            'wali_status' => 'required|in:Ayah,Ibu,Wali',
        ]);
        
        // Get the student
        $siswa = Model::findOrFail($requestData['siswa_id']);
        
        // Get the guardian
        $wali = User::findOrFail($requestData['wali_id']);
        
        // Make sure the guardian is actually a wali
        if ($wali->akses != 'wali') {
            return redirect()->back()->with('error', 'User yang dipilih bukan wali murid');
        }
        
        // Update the student with guardian info
        $siswa->update([
            'wali_id' => $requestData['wali_id'],
            'wali_status' => $requestData['wali_status'],
        ]);
        
        // Redirect back to wali detail page with success message
        return redirect()
            ->route('siswa.wali', $siswa->id)
            ->with('success', 'Siswa berhasil ditambahkan ke Wali Murid');
    }
    
    /**
     * Remove a student from its guardian.
     */
    public function hapusDariWali(Request $request)
    {
        // Validate request
        $requestData = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
        ]);
        
        // Get the student
        $siswa = Model::findOrFail($requestData['siswa_id']);
        
        // Store the wali ID for redirect
        $waliId = $siswa->wali_id;
        
        // Check if the student has a guardian
        if (!$waliId) {
            return redirect()->back()->with('error', 'Siswa tidak memiliki wali murid');
        }
        
        // Update the student to remove guardian info
        $siswa->update([
            'wali_id' => null,
            'wali_status' => null,
        ]);
        
        // Redirect back to wali detail page with success message
        return redirect()
            ->route('wali.show', $waliId)
            ->with('success', 'Siswa berhasil dihapus dari Wali Murid');
    }

    /**
     * Export data siswa ke excel
     */
    public function export() 
    {
        return Excel::download(new SiswaExport, 'data-siswa-'.date('Y-m-d').'.xlsx');
    }
    
    /**
     * Download template import excel
     */
    public function importTemplate()
    {
        return Excel::download(new SiswaImportTemplate, 'template-import-siswa.xlsx');
    }
    
    /**
     * Import form
     */
    public function importForm()
    {
        return view('operator.siswa_import', [
            'title' => 'Import Data Siswa',
            'routePrefix' => $this->routePrefix
        ]);
    }
    
    /**
     * Import excel to database
     */
    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ], [
            'file.required' => 'File excel wajib diupload',
            'file.mimes' => 'File harus berupa excel (.xlsx atau .xls)'
        ]);
        
        try {
            // File berhasil divalidasi
            $file = $request->file('file');
            $import = new SiswaImport();
            
            // Import file Excel dengan menangkap exception
            Excel::import($import, $file);
            
            // Jika berhasil, redirect dengan pesan sukses
            return redirect()
                ->route($this->routePrefix . '.index')
                ->with('success', 'Data Siswa berhasil diimport. Total data: ' . $import->getSuccessCount());
                
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Menangkap error validasi dari Laravel Excel
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = 'Baris ke-' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan validasi: ' . implode('<br>', $errors));
                
        } catch (\Exception $e) {
            // Menangkap error umum
            \Log::error('Error importing students: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}