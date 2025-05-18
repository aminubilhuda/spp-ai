<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Biaya;
use App\Models\Siswa;
use App\Models\Jurusan;
use App\Models\Tagihan;
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    private $viewIndex = 'tagihan_index';
    private $viewCreate = 'tagihan_form';
    private $viewEdit = 'tagihan_form';
    private $viewShow = 'tagihan_show';
    private $routePrefix = 'tagihan';

    public function index(Request $request)
    {
        // Query dasar untuk tagihan dengan eager loading yang lebih efisien
        $baseQuery = Tagihan::query()->select('tagihans.siswa_id', 
                     DB::raw('COUNT(DISTINCT tagihan_details.id) as total_tagihan'), 
                     DB::raw('SUM(tagihan_details.jumlah_biaya) as total_nilai'),
                     DB::raw('MAX(tagihans.created_at) as latest_created'))
            ->join('tagihan_details', 'tagihans.id', '=', 'tagihan_details.tagihan_id')
            ->with(['siswa' => function($q) {
                $q->select('id', 'nama', 'nisn', 'kelas', 'jurusan_id', 'angkatan')
                  ->with('jurusan:id,nama'); 
            }])
            ->groupBy('tagihans.siswa_id');
        
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
          // Filter berdasarkan status tagihan detail
        if ($request->has('status') && !empty($request->status)) {
            $baseQuery->whereHas('tagihan_details', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Urutkan berdasarkan latest_created yang sudah diagregasi
        $models = $baseQuery->orderBy('latest_created', 'desc')->paginate(10)->withQueryString();

        return view('operator.' . $this->viewIndex, [
            'models' => $models,
            'routePrefix' => $this->routePrefix,
            'title' => 'Data Tagihan Siswa',
            'angkatan' => Siswa::select('angkatan')->distinct()->pluck('angkatan'),
            'jurusan' => Jurusan::pluck('nama', 'id')
        ]);
    }

    public function create()
    {
        // Pastikan data biaya memiliki properti jumlah dengan eager loading
        $biaya = Biaya::select('id', 'nama', 'jumlah')->get();
        
        $data = [
            'model' => new Tagihan(),
            'method' => 'POST',
            'route' => $this->routePrefix . '.store',
            'button' => 'SIMPAN',
            'title' => 'Tambah Tagihan',
            'biaya' => $biaya,
            'angkatan' => Siswa::select('angkatan')->distinct()->pluck('angkatan'),
            'kelas' => ['X', 'XI', 'XII'],
            'jurusan' => Jurusan::pluck('nama', 'id')->all()
        ];
        return view('operator.' . $this->viewCreate, $data);
    }    public function store(Request $request)
    {
        try {
            \DB::beginTransaction();
            
            $requestData = $request->validate([
                'biaya_id' => 'required|array',
                'biaya_id.*' => 'exists:biayas,id',
                'angkatan' => 'required',
                'jurusan' => 'nullable|exists:jurusans,id',
                'kelas' => 'nullable',
                'tanggal_tagihan' => 'required|date',
                'tanggal_jatuh_tempo' => 'required|date',
                'keterangan' => 'nullable|string'
            ]);

            // Data biaya
            $biaya_id_array = $requestData['biaya_id'];
            
            // Data siswa yang akan ditagih
            $siswaQuery = Siswa::where('angkatan', $requestData['angkatan']);
            
            if (!empty($requestData['jurusan'])) {
                $siswaQuery->where('jurusan_id', $requestData['jurusan']);
            }
            
            if (!empty($requestData['kelas'])) {
                $siswaQuery->where('kelas', $requestData['kelas']);
            }
            
            $siswa = $siswaQuery->get();
            $count = 0;
            
            foreach($siswa as $item) {                $tagihanData = [
                    'user_id' => auth()->user()->id,
                    'denda' => 0,
                    'siswa_id' => $item->id,
                    'angkatan' => $requestData['angkatan'],
                    'jurusan' => !empty($requestData['jurusan']) ? $requestData['jurusan'] : $item->jurusan_id,
                    'kelas' => !empty($requestData['kelas']) ? $requestData['kelas'] : $item->kelas,
                    'tanggal_tagihan' => $requestData['tanggal_tagihan'],
                    'tanggal_jatuh_tempo' => $requestData['tanggal_jatuh_tempo'],
                    'keterangan' => $requestData['keterangan'] ?? null,
                ];
                
                $tagihan = Tagihan::create($tagihanData);
                
                foreach($biaya_id_array as $biaya_id) {
                    $biaya = Biaya::findOrFail($biaya_id);
                    
                    if (!$biaya->jumlah) {
                        throw new \Exception("Jumlah biaya tidak boleh kosong untuk biaya: " . $biaya->nama);
                    }
                    
                    $tagihan->tagihan_details()->create([
                        'nama_biaya' => $biaya->nama ?? 'Tidak ada nama',
                        'jumlah_biaya' => $biaya->jumlah,
                        'tagihan_id' => $tagihan->id
                    ]);
                    
                    $count++;
                }
            }
            
            \DB::commit();
            return redirect()->route($this->routePrefix . '.index')
                ->with('success', 'Data berhasil ditambah untuk ' . $count . ' tagihan');
                
        } catch (\Exception $e) {
            \DB::rollBack();
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
        // Optimasi query dengan select kolom yang dibutuhkan saja
        $siswa = Siswa::select('id', 'nama', 'nisn', 'kelas', 'jurusan_id', 'angkatan')
            ->with('jurusan:id,nama')
            ->get();
            
        $biaya = Biaya::select('id', 'nama', 'jumlah')->get();
        
        return view('operator.' . $this->viewEdit, [
            'title' => 'Edit Data Tagihan',
            'tagihan' => $tagihan,
            'siswa' => $siswa,
            'biaya' => $biaya
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
            ]);

            $biaya = Biaya::findOrFail($requestData['biaya_id']);
            $siswa = Siswa::findOrFail($requestData['siswa_id']);
            
            // Update main tagihan
            $tagihan->update([                'siswa_id' => $requestData['siswa_id'],
                'angkatan' => $siswa->angkatan,
                'kelas' => $siswa->kelas,
                'jurusan' => $siswa->jurusan_id,
                'tanggal_tagihan' => $requestData['tanggal_tagihan'],
                'tanggal_jatuh_tempo' => $requestData['tanggal_jatuh_tempo'],
                'keterangan' => $requestData['keterangan'],
                'denda' => $requestData['denda'],
                'user_id' => auth()->user()->id,
            ]);

            // Delete existing details
            $tagihan->tagihan_details()->delete();
            
            // Create new detail
            $tagihan->tagihan_details()->create([
                'nama_biaya' => $biaya->nama,
                'jumlah_biaya' => $biaya->jumlah
            ]);

            \DB::commit();
            
            return redirect()->route($this->routePrefix . '.index')
                ->with('success', 'Data berhasil diupdate');
                
        } catch (\Exception $e) {
            \DB::rollBack();
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
            // Cek apakah siswa ada
            $siswa = Siswa::findOrFail($siswaId);
            
            // Mengambil data tagihan dengan detail biaya
            $tagihan = Tagihan::with('tagihan_details')
                ->where('siswa_id', $siswaId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Memuat relasi jurusan (eager loading)
            $siswa->load('jurusan:id,nama');
            
            return view('operator.tagihan_siswa_detail', [
                'title' => 'Detail Tagihan Siswa: ' . $siswa->nama,
                'siswa' => $siswa,
                'routePrefix' => $this->routePrefix,
                'tagihan' => $tagihan
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
            $detail = \App\Models\TagihanDetail::findOrFail($id);
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
    }    public function detail($id)
    {
        try {
            \Log::info('Fetching tagihan details for ID: ' . $id);
            
            $tagihan = Tagihan::with(['tagihan_details', 'pembayaran', 'siswa'])->findOrFail($id);
            \Log::info('Tagihan found:', ['tagihan' => $tagihan->toArray()]);

            $totalTagihan = $tagihan->tagihan_details->sum('jumlah_biaya');
            $totalDibayar = $tagihan->pembayaran->sum('jumlah_dibayar');
            $sisaBayar = max(0, $totalTagihan - $totalDibayar);

            \Log::info('Calculated amounts:', [
                'total_tagihan' => $totalTagihan,
                'total_dibayar' => $totalDibayar,
                'sisa_bayar' => $sisaBayar
            ]);            $status = 'belum_lunas';
            if ($sisaBayar <= 0) {
                $status = 'lunas';
            } elseif ($totalDibayar > 0) {
                $status = 'angsur';
            }

            $response = [
                'tagihan' => $tagihan,
                'total_tagihan' => $totalTagihan,
                'total_bayar' => $totalDibayar,
                'remaining_amount' => $sisaBayar,
                'status' => $status,
                'detail' => [
                    'nama_siswa' => $tagihan->siswa->nama ?? 'Tidak ditemukan',
                    'kelas' => $tagihan->siswa->kelas ?? '-',
                ]
            ];

            \Log::info('Sending response:', $response);
            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Error in detail method:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Gagal mengambil data tagihan: ' . $e->getMessage()
            ], 500);
        }
    }
}