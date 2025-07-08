<?php

namespace App\Http\Controllers;

use App\Models\Biaya;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Jurusan;
use App\Models\TagihanDetail;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use PDF;

class TagihanController extends Controller
{
    private $viewIndex = 'tagihan_index';
    private $viewCreate = 'tagihan_form';
    private $viewEdit = 'tagihan_form';
    private $viewShow = 'tagihan_show';
    private $routePrefix = 'tagihan';

    public function index(Request $request)
    {
        $tahunPelajarans = TahunPelajaran::orderByDesc('is_aktif')->orderBy('nama')->get();
        $tahunAktif = $tahunPelajarans->firstWhere('is_aktif', 1);
        $tahunPelajaranId = $request->get('tahun_pelajaran_id', $tahunAktif?->id);

        // Query dasar untuk tagihan dengan eager loading yang lebih efisien
        $baseQuery = Tagihan::query()
            ->select('tagihans.siswa_id', 
                     \DB::raw('COUNT(DISTINCT tagihans.id) as total_tagihan'), 
                     \DB::raw('SUM(tagihan_details.jumlah_biaya) as total_nilai'),
                     \DB::raw('MAX(tagihans.created_at) as latest_created'))
            ->join('tagihan_details', 'tagihans.id', '=', 'tagihan_details.tagihan_id')
            ->with(['siswa' => function($q) {
                $q->select('id', 'nama', 'nisn', 'kelas', 'jurusan_id', 'angkatan')
                  ->with('jurusan:id,nama'); 
            }])
            ->groupBy('tagihans.siswa_id');
        
        // Filter tahun pelajaran aktif
        if ($tahunPelajaranId) {
            $baseQuery->where('tagihans.tahun_pelajaran_id', $tahunPelajaranId);
        }
        
        // Filter tahun (default tahun sekarang)
        $tahun = $request->get('tahun', date('Y'));
        $baseQuery->whereYear('tagihans.tanggal_tagihan', $tahun);
        
        // Filter pencarian
        if ($request->has('search')) {
            $search = $request->search;
            
            $baseQuery->whereHas('siswa', function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('nisn', 'like', '%' . $search . '%');
            });
        }
        
        // Filter berdasarkan angkatan
        if ($request->has('angkatan') && !empty($request->angkatan)) {
            $baseQuery->whereHas('siswa', function($q) use ($request) {
                $q->where('angkatan', $request->angkatan);
            });
        }
        
        // Filter berdasarkan kelas
        if ($request->has('kelas') && !empty($request->kelas)) {
            $baseQuery->whereHas('siswa', function($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }
        
        // Filter berdasarkan jurusan
        if ($request->has('jurusan') && !empty($request->jurusan)) {
            $baseQuery->whereHas('siswa', function($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan);
            });
        }
          // Filter berdasarkan status tagihan
        if ($request->has('status') && !empty($request->status)) {
            $baseQuery->whereHas('tagihan_details', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Urutkan berdasarkan latest_created yang sudah diagregasi
        $models = $baseQuery->orderBy('latest_created', 'desc')->paginate(10)->withQueryString();

        // Generate array tahun untuk filter (5 tahun ke belakang sampai tahun sekarang)
        $tahunList = collect(range(date('Y')-4, date('Y')))->sortDesc();

        return view('operator.' . $this->viewIndex, [
            'models' => $models,
            'routePrefix' => $this->routePrefix,
            'title' => 'Data Tagihan Siswa',
            'angkatan' => Siswa::select('angkatan')->distinct()->pluck('angkatan'),
            'jurusan' => Jurusan::pluck('nama', 'id'),
            'tahunPelajarans' => $tahunPelajarans,
            'tahunPelajaranId' => $tahunPelajaranId,
            'tahunAktif' => $tahunAktif,
            'tahunList' => $tahunList,
            'tahunSelected' => $tahun
        ]);
    }

    public function create()
    {
        // Ambil biaya dengan struktur parent-child
        $biaya = Biaya::with('children')
                     ->whereNull('parent_id') // Hanya ambil parent biaya
                     ->get();
        $tahunPelajarans = TahunPelajaran::orderByDesc('is_aktif')->orderBy('nama')->get();
        $tahunAktif = $tahunPelajarans->firstWhere('is_aktif', 1);
        $data = [
            'model' => new Tagihan(),
            'method' => 'POST',
            'route' => $this->routePrefix . '.store',
            'button' => 'SIMPAN',
            'title' => 'Tambah Tagihan',
            'biaya' => $biaya,
            'angkatan' => Siswa::select('angkatan')->distinct()->pluck('angkatan'),
            'kelas' => ['X', 'XI', 'XII'],
            'jurusan' => Jurusan::pluck('nama', 'id')->all(),
            'tahunPelajarans' => $tahunPelajarans,
            'tahunAktif' => $tahunAktif,
        ];
        return view('operator.' . $this->viewCreate, $data);
    }    
    
    public function store(Request $request)
    {
        try {
            \DB::beginTransaction();
            
            $requestData = $request->validate([
                'biaya_id' => 'required|array',
                'biaya_id.*' => 'exists:biayas,id',
                'mode_siswa' => 'required|in:filter,single',
                'siswa_id' => 'nullable|exists:siswas,id',
                'angkatan' => 'nullable|string',
                'jurusan' => 'nullable|exists:jurusans,id',
                'kelas' => 'nullable',
                'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
                'tanggal_tagihan' => 'required|date',
                'tanggal_jatuh_tempo' => 'required|date',
                'keterangan' => 'nullable|string'
            ], [
                'biaya_id.required' => 'Pilih minimal satu biaya',
                'biaya_id.array' => 'Format biaya tidak valid',
                'biaya_id.*.exists' => 'Biaya yang dipilih tidak valid',
                'mode_siswa.required' => 'Pilih mode pemilihan siswa',
                'mode_siswa.in' => 'Mode pemilihan siswa tidak valid',
                'siswa_id.exists' => 'Siswa yang dipilih tidak valid',
                'jenis_kelamin.in' => 'Jenis kelamin yang dipilih tidak valid',
                'tanggal_tagihan.required' => 'Tanggal tagihan wajib diisi',
                'tanggal_tagihan.date' => 'Format tanggal tagihan tidak valid',
                'tanggal_jatuh_tempo.required' => 'Tanggal jatuh tempo wajib diisi',
                'tanggal_jatuh_tempo.date' => 'Format tanggal jatuh tempo tidak valid',
            ]);

            // Validasi tambahan berdasarkan mode
            if ($requestData['mode_siswa'] === 'single' && empty($requestData['siswa_id'])) {
                throw new \Exception('Pilih siswa yang akan ditagih');
            }

            // Data biaya
            $biaya_id_array = $requestData['biaya_id'];
            
            // Data siswa yang akan ditagih berdasarkan mode
            if ($requestData['mode_siswa'] === 'single') {
                // Mode single student
                $siswa = Siswa::with('jurusan')
                             ->currentStatus('Aktif')
                             ->where('id', $requestData['siswa_id'])
                             ->get();
                             
                if ($siswa->isEmpty()) {
                    throw new \Exception('Siswa yang dipilih tidak ditemukan atau tidak aktif');
                }
            } else {
                // Mode filter (bulk)
                $siswaQuery = Siswa::query();
                
                // Tambahkan filter status Aktif
                $siswaQuery->currentStatus('Aktif');

                if (!empty($requestData['angkatan'])) {
                    $siswaQuery->where('angkatan', $requestData['angkatan']);
                }

                if (!empty($requestData['jurusan'])) {
                    $siswaQuery->where('jurusan_id', $requestData['jurusan']);
                }
                
                if (!empty($requestData['kelas'])) {
                    $siswaQuery->where('kelas', $requestData['kelas']);
                }
                
                if (!empty($requestData['jenis_kelamin'])) {
                    $siswaQuery->where('jenis_kelamin', $requestData['jenis_kelamin']);
                }
                
                $siswa = $siswaQuery->get();
            }
            
            // Validasi jika tidak ada siswa yang ditemukan
            if ($siswa->isEmpty()) {
                throw new \Exception('Tidak ada siswa yang sesuai dengan kriteria yang dipilih');
            }
            
            $count = 0;
            
            foreach($siswa as $item) {
                // Ambil tahun_pelajaran_id dari request jika ada, jika tidak pakai tahun pelajaran aktif
                $tahunPelajaranId = $requestData['tahun_pelajaran_id'] ?? null;
                if (!$tahunPelajaranId) {
                    $tahunAktif = TahunPelajaran::where('is_aktif', 1)->first();
                    $tahunPelajaranId = $tahunAktif ? $tahunAktif->id : null;
                }

                // Jika generate 1 tahun dicentang
                if ($request->has('generate_1_tahun')) {
                    $start = \Carbon\Carbon::create(2025, 7, 1);
                    $end = \Carbon\Carbon::create(2026, 6, 1);

                    for ($date = $start->copy(); $date->lte($end); $date->addMonth()) {
                        // Cek duplikasi tagihan
                        $exists = Tagihan::where('siswa_id', $item->id)
                            ->whereMonth('tanggal_tagihan', $date->month)
                            ->whereYear('tanggal_tagihan', $date->year)
                            ->where('tahun_pelajaran_id', $tahunPelajaranId)
                            ->exists();
                        if ($exists) continue;

                        $tagihanData = [
                            'user_id' => auth()->user()->id,
                            'denda' => 0,
                            'siswa_id' => $item->id,
                            'angkatan' => $item->angkatan,
                            'jurusan' => $item->jurusan_id,
                            'kelas' => $item->kelas,
                            'tahun_pelajaran_id' => $tahunPelajaranId,
                            'tanggal_tagihan' => $date->format('Y-m-01'),
                            'tanggal_jatuh_tempo' => $date->format('Y-m-28'),
                            'keterangan' => $requestData['keterangan'] ?? null,
                        ];

                        $tagihan = Tagihan::create($tagihanData);

                        foreach($biaya_id_array as $biaya_id) {
                            $biaya = Biaya::with('children')->findOrFail($biaya_id);

                            if ($biaya->isParent() && $biaya->children->count() > 0) {
                                foreach ($biaya->children as $child) {
                                    if (!$child->jumlah) {
                                        throw new \Exception("Jumlah biaya tidak boleh kosong untuk biaya: " . $child->nama);
                                    }
                                    $tagihan->tagihan_details()->create([
                                        'nama_biaya' => $child->nama,
                                        'jumlah_biaya' => $child->jumlah,
                                        'tagihan_id' => $tagihan->id,
                                        'biaya_id' => $child->id,
                                        'status' => 'baru'
                                    ]);
                                    $count++;
                                }
                            } else {
                                if (!$biaya->jumlah) {
                                    throw new \Exception("Jumlah biaya tidak boleh kosong untuk biaya: " . $biaya->nama);
                                }
                                $tagihan->tagihan_details()->create([
                                    'nama_biaya' => $biaya->nama,
                                    'jumlah_biaya' => $biaya->jumlah,
                                    'tagihan_id' => $tagihan->id,
                                    'biaya_id' => $biaya->id,
                                    'status' => 'baru'
                                ]);
                                $count++;
                            }
                        }

                        // Kirim notifikasi ke wali murid
                        if ($item->wali) {
                            $item->wali->notify(new \App\Notifications\TagihanNotification($tagihan));
                        }
                    }
                } else {
                    // Proses single tagihan seperti biasa
                    $tagihanData = [
                        'user_id' => auth()->user()->id,
                        'denda' => 0,
                        'siswa_id' => $item->id,
                        'angkatan' => $item->angkatan,
                        'jurusan' => $item->jurusan_id,
                        'kelas' => $item->kelas,
                        'tahun_pelajaran_id' => $tahunPelajaranId,
                        'tanggal_tagihan' => $requestData['tanggal_tagihan'],
                        'tanggal_jatuh_tempo' => $requestData['tanggal_jatuh_tempo'],
                        'keterangan' => $requestData['keterangan'] ?? null,
                    ];

                    $tagihan = Tagihan::create($tagihanData);

                    foreach($biaya_id_array as $biaya_id) {
                        $biaya = Biaya::with('children')->findOrFail($biaya_id);

                        if ($biaya->isParent() && $biaya->children->count() > 0) {
                            foreach ($biaya->children as $child) {
                                if (!$child->jumlah) {
                                    throw new \Exception("Jumlah biaya tidak boleh kosong untuk biaya: " . $child->nama);
                                }
                                $tagihan->tagihan_details()->create([
                                    'nama_biaya' => $child->nama,
                                    'jumlah_biaya' => $child->jumlah,
                                    'tagihan_id' => $tagihan->id,
                                    'biaya_id' => $child->id,
                                    'status' => 'baru'
                                ]);
                                $count++;
                            }
                        } else {
                            if (!$biaya->jumlah) {
                                throw new \Exception("Jumlah biaya tidak boleh kosong untuk biaya: " . $biaya->nama);
                            }
                            $tagihan->tagihan_details()->create([
                                'nama_biaya' => $biaya->nama,
                                'jumlah_biaya' => $biaya->jumlah,
                                'tagihan_id' => $tagihan->id,
                                'biaya_id' => $biaya->id,
                                'status' => 'baru'
                            ]);
                            $count++;
                        }
                    }

                    // Kirim notifikasi ke wali murid
                    if ($item->wali) {
                        $item->wali->notify(new \App\Notifications\TagihanNotification($tagihan));
                    }
                }
            }
            
            \DB::commit();

            // Response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil membuat ' . $count . ' tagihan',
                    'redirect' => route($this->routePrefix . '.index')
                ]);
            }

            // Response untuk non-AJAX request
            return redirect()->route($this->routePrefix . '.index')
                ->with('success', 'Data berhasil ditambah untuk ' . $count . ' tagihan');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            
            // Response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambah tagihan: ' . $e->getMessage()
                ], 422);
            }

            // Response untuk non-AJAX request
            return redirect()->route($this->routePrefix . '.index')
                ->with('error', 'Gagal menambah tagihan: ' . $e->getMessage());
        }
    }

    public function show(Tagihan $tagihan)
    {
        // Eager loading untuk mengambil relasi siswa, jurusan, dan tagihan_details
        $tagihan->load([
            'siswa' => function($q) {
                $q->with('jurusan:id,nama');
            },
            'tagihan_details'
        ]);
        
        return view('operator.' . $this->viewShow, [
            'title' => 'Detail Tagihan',
            'tagihan' => $tagihan
        ]);
    }

    public function edit(Tagihan $tagihan)
    {
        $siswa = Siswa::select('id', 'nama', 'nisn', 'kelas', 'jurusan_id', 'angkatan')
            ->with('jurusan:id,nama')
            ->get();
        $biaya = Biaya::with('children')->whereNull('parent_id')->get();
        $tahunPelajarans = TahunPelajaran::orderByDesc('is_aktif')->orderBy('nama')->get();
        $tahunAktif = $tahunPelajarans->firstWhere('is_aktif', 1);
        return view('operator.' . $this->viewEdit, [
            'title' => 'Edit Data Tagihan',
            'tagihan' => $tagihan,
            'siswa' => $siswa,
            'biaya' => $biaya,
            'tahunPelajarans' => $tahunPelajarans,
            'tahunAktif' => $tahunAktif,
        ]);
    }

    public function update(Request $request, Tagihan $tagihan)
    {
        try {
            \DB::beginTransaction();
            
            $requestData = $request->validate([
                'biaya_id' => 'required|exists:biayas,id',
                'siswa_id' => 'required|exists:siswas,id',
                'tanggal_tagihan' => 'required|date',
                'tanggal_jatuh_tempo' => 'required|date',
                'keterangan' => 'nullable|string',
                'denda' => 'required|numeric'
            ], [
                'biaya_id.required' => 'Biaya wajib dipilih',
                'biaya_id.exists' => 'Biaya yang dipilih tidak valid',
                'siswa_id.required' => 'Siswa wajib dipilih',
                'siswa_id.exists' => 'Siswa yang dipilih tidak valid',
                'tanggal_tagihan.required' => 'Tanggal tagihan wajib diisi',
                'tanggal_tagihan.date' => 'Format tanggal tagihan tidak valid',
                'tanggal_jatuh_tempo.required' => 'Tanggal jatuh tempo wajib diisi',
                'tanggal_jatuh_tempo.date' => 'Format tanggal jatuh tempo tidak valid',
                'denda.required' => 'Denda wajib diisi',
                'denda.numeric' => 'Denda harus berupa angka'
            ]);

            $biaya = Biaya::findOrFail($requestData['biaya_id']);
            $siswa = Siswa::findOrFail($requestData['siswa_id']);
            
            // Ambil tahun_pelajaran_id dari request jika ada, jika tidak pakai tahun pelajaran aktif
            $tahunPelajaranId = $requestData['tahun_pelajaran_id'] ?? null;
            if (!$tahunPelajaranId) {
                $tahunAktif = TahunPelajaran::where('is_aktif', 1)->first();
                $tahunPelajaranId = $tahunAktif ? $tahunAktif->id : null;
            }
            // Update main tagihan
            $tagihan->update([
                'siswa_id' => $requestData['siswa_id'],
                'angkatan' => $siswa->angkatan,
                'jurusan' => $siswa->jurusan_id,
                'kelas' => $siswa->kelas,
                'tahun_pelajaran_id' => $tahunPelajaranId,
                'tanggal_tagihan' => $requestData['tanggal_tagihan'],
                'tanggal_jatuh_tempo' => $requestData['tanggal_jatuh_tempo'],
                'keterangan' => $requestData['keterangan'] ?? null,
            ]);

            // Delete existing details
            $tagihan->tagihan_details()->delete();
            
            // Create new detail
            $tagihan->tagihan_details()->create([
                'nama_biaya' => $biaya->nama,
                'jumlah_biaya' => $biaya->jumlah,
                'status' => 'baru'
            ]);

            \DB::commit();
            
            // Response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil diupdate',
                    'redirect' => route($this->routePrefix . '.index')
                ]);
            }

            // Response untuk non-AJAX request
            return redirect()->route($this->routePrefix . '.index')
                ->with('success', 'Data berhasil diupdate');
                
        } catch (\Exception $e) {
            \DB::rollBack();

            // Response untuk AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate tagihan: ' . $e->getMessage()
                ], 422);
            }

            // Response untuk non-AJAX request
            return redirect()->route($this->routePrefix . '.index')
                ->with('error', 'Gagal mengupdate tagihan: ' . $e->getMessage());
        }
    }

    public function destroy($siswa_id)
    {
        // Delete all tagihan records for this student
        Tagihan::where('siswa_id', $siswa_id)->delete();
        return redirect()->route($this->routePrefix . '.index')->with('success', 'Data tagihan berhasil dihapus');
    }

    public function deleteByCategory(Request $request)
    {
        try {
            \DB::beginTransaction();
            
            // Validasi input
            $request->validate([
                'angkatan' => 'nullable',
                'jurusan' => 'nullable',
                'kelas' => 'nullable|string',
            ]);
            
            // Mulai membangun query
            $query = Tagihan::query();
            
            // Tambahkan filter sesuai parameter yang dipilih
            if ($request->filled('angkatan')) {
                $query->where('angkatan', $request->angkatan);
            }
            
            if ($request->filled('jurusan')) {
                $query->where('jurusan', $request->jurusan);
            }
            
            if ($request->filled('kelas')) {
                $query->where('kelas', $request->kelas);
            }
            
            // Jika tidak ada filter yang dipilih, kembalikan dengan pesan error
            if (!$request->filled('angkatan') && !$request->filled('jurusan') && !$request->filled('kelas')) {
                return redirect()->route($this->routePrefix . '.index')
                    ->with('error', 'Silakan pilih minimal satu kriteria untuk menghapus tagihan');
            }
            
            // Hitung jumlah data yang akan dihapus
            $count = $query->count();
            
            if ($count === 0) {
                return redirect()->route($this->routePrefix . '.index')
                    ->with('info', 'Tidak ada data yang sesuai dengan kriteria yang dipilih');
            }
            
            // Hapus data yang sesuai dengan kriteria
            $query->delete();
            
            \DB::commit();
            
            return redirect()->route($this->routePrefix . '.index')
                ->with('success', "Berhasil menghapus $count data tagihan yang sesuai dengan kriteria");
                
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route($this->routePrefix . '.index')
                ->with('error', 'Gagal menghapus tagihan: ' . $e->getMessage());
        }
    }

    // Menambahkan method baru untuk menampilkan detail tagihan siswa
    public function showByStudent($siswaId)
    {
        try {
            $siswa = Siswa::findOrFail($siswaId);
    
            // Ambil semua tahun pelajaran
            $tahunPelajarans = TahunPelajaran::orderByDesc('is_aktif')->orderBy('nama')->get();
            $tahunAktif = $tahunPelajarans->firstWhere('is_aktif', 1);
            $tahunPelajaranId = request('tahun_pelajaran_id', $tahunAktif?->id);
    
            // Filter tagihan sesuai tahun pelajaran yang dipilih
            $tagihan = Tagihan::with('tagihan_details')
                ->where('siswa_id', $siswaId)
                ->where('tahun_pelajaran_id', $tahunPelajaranId)
                ->orderBy('created_at', 'desc')
                ->get();
    
            $siswa->load('jurusan:id,nama');
    
            return view('operator.tagihan_siswa_detail', [
                'title' => 'Detail Tagihan Siswa: ' . $siswa->nama,
                'siswa' => $siswa,
                'routePrefix' => $this->routePrefix,
                'tagihan' => $tagihan,
                'tahunPelajarans' => $tahunPelajarans,
                'tahunAktif' => $tahunAktif,
                'tahunPelajaranId' => $tahunPelajaranId,
            ]);
        } catch (\Exception $e) {
            return redirect()->route($this->routePrefix . '.index')
                ->with('error', 'Gagal menampilkan detail: ' . $e->getMessage());
        }
    }

    /**
     * Delete a specific tagihan detail
     */
    public function destroyDetail($id)
    {
        try {
            \DB::beginTransaction();
            
            // Find and delete the tagihan detail
            $detail = TagihanDetail::findOrFail($id);
            $tagihanId = $detail->tagihan_id;
            
            // Get the parent tagihan
            $tagihan = Tagihan::findOrFail($tagihanId);
            
            // Delete the detail
            $detail->delete();
            
            // If this was the last detail, delete the parent tagihan
            if ($tagihan->tagihan_details()->count() === 0) {
                $tagihan->delete();
                \DB::commit();
                return redirect()->route($this->routePrefix . '.index')
                    ->with('success', 'Tagihan berhasil dihapus karena tidak ada item tersisa');
            }

            \DB::commit();
            
            // Redirect back to the tagihan detail page
            return redirect()->route('tagihan.showByStudent', $tagihan->siswa_id)
                ->with('success', 'Item tagihan berhasil dihapus');

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'Gagal menghapus item tagihan: ' . $e->getMessage());
        }
    }   

    public function updateDetail(Request $request, $id)
    {
        try {
            \DB::beginTransaction();
            
            $detail = TagihanDetail::findOrFail($id);
            
            // Validate request
            $validated = $request->validate([
                'jumlah_biaya' => 'required|numeric|min:0',
            ], [
                'jumlah_biaya.required' => 'Jumlah biaya wajib diisi.',
                'jumlah_biaya.numeric' => 'Jumlah biaya harus berupa angka.',
                'jumlah_biaya.min' => 'Jumlah biaya tidak boleh kurang dari 0.',
            ]);
            
            // Check if there are any payments
            if($detail->pembayaran()->exists()) {
                throw new \Exception('Tidak dapat mengubah tagihan yang sudah memiliki pembayaran');
            }
            
            // Update detail
            $detail->update([
                'jumlah_biaya' => $validated['jumlah_biaya']
            ]);
            
            \DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Detail tagihan berhasil diupdate'
            ]);
                
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate detail tagihan: ' . $e->getMessage()
            ], 400);
        }
    }

    public function detailInfo($id)
    {
        try {
            \Log::info('Fetching tagihan detail info for ID: ' . $id);
            
            $detail = TagihanDetail::with(['tagihan.siswa'])->findOrFail($id);
            
            $totalBayar = $detail->pembayaran->where('status_konfirmasi', 'Sudah Dikonfirmasi')->sum('jumlah_dibayar');
            $sisaBayar = max(0, $detail->jumlah_biaya - $totalBayar);

            $response = [
                'detail' => [
                    'nama_siswa' => $detail->tagihan->siswa->nama ?? 'Tidak ditemukan',
                    'kelas' => $detail->tagihan->siswa->kelas ?? '-',
                    'nama_biaya' => $detail->nama_biaya,
                ],
                'total_tagihan' => $detail->jumlah_biaya,
                'total_bayar' => $totalBayar,
                'remaining_amount' => $sisaBayar,
                'status' => $detail->status
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Error in detailInfo method:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Gagal mengambil data tagihan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rekapTagihanPdf($siswa_id)
    {
        $siswa = Siswa::with('jurusan')->findOrFail($siswa_id);
        $tagihan_details = TagihanDetail::with(['tagihan', 'pembayaran'])
            ->whereHas('tagihan', function ($q) use ($siswa_id) {
                $q->where('siswa_id', $siswa_id);
            })
            ->get();

        $pdf = PDF::loadView('operator.tagihan_rekap', [
            'siswa' => $siswa,
            'tagihan_details' => $tagihan_details
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('rekap_tagihan_' . $siswa->nama . '.pdf');
    }
}