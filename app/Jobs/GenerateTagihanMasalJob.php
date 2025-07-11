<?php

namespace App\Jobs;

use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\TagihanDetail;
use App\Models\Biaya;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use App\Notifications\OperatorTagihanRekapNotification;

class GenerateTagihanMasalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $siswaIds;
    protected $biayaIds;
    protected $tahunPelajaranId;
    protected $tanggalMulai;
    protected $tanggalAkhir;
    protected $keterangan;
    protected $operatorId;
    protected $kirimNotifikasiWali;
    protected $kirimNotifikasiOperator;

    /**
     * Create a new job instance.
     */
    public function __construct($siswaIds, $biayaIds, $tahunPelajaranId, $tanggalMulai, $tanggalAkhir, $keterangan, $operatorId, $kirimNotifikasiWali = true, $kirimNotifikasiOperator = false)
    {
        $this->siswaIds = $siswaIds;
        $this->biayaIds = $biayaIds;
        $this->tahunPelajaranId = $tahunPelajaranId;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->keterangan = $keterangan;
        $this->operatorId = $operatorId;
        $this->kirimNotifikasiWali = $kirimNotifikasiWali;
        $this->kirimNotifikasiOperator = $kirimNotifikasiOperator;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('Job dimulai: GenerateTagihanMasalJob', [
            'siswa_ids' => $this->siswaIds,
            'biaya_ids' => $this->biayaIds,
            'tahun_pelajaran_id' => $this->tahunPelajaranId,
            'tanggal_mulai' => $this->tanggalMulai,
            'tanggal_akhir' => $this->tanggalAkhir,
            'operator_id' => $this->operatorId
        ]);
        try {
            $siswaList = Siswa::with(['wali'])->whereIn('id', $this->siswaIds)->get();
            $biayaList = Biaya::with('children')->whereIn('id', $this->biayaIds)->get();
            \Log::info('Jumlah siswa:', ['count' => $siswaList->count()]);
            \Log::info('Jumlah biaya:', ['count' => $biayaList->count()]);
            \Log::info('Tanggal mulai:', ['tanggal' => $this->tanggalMulai]);
            \Log::info('Tanggal akhir:', ['tanggal' => $this->tanggalAkhir]);
            $rekapTagihan = [];
            $operator = User::find($this->operatorId);

            foreach ($siswaList as $siswa) {
                $tagihanSiswa = [];
                $bulan = Carbon::parse($this->tanggalMulai)->copy();
                $end = Carbon::parse($this->tanggalAkhir)->copy();
                while ($bulan->lte($end)) {
                    \Log::info('Proses bulan:', ['bulan' => $bulan->format('Y-m')]);
                    // Cek duplikasi tagihan
                    $exists = Tagihan::where('siswa_id', $siswa->id)
                        ->whereMonth('tanggal_tagihan', $bulan->month)
                        ->whereYear('tanggal_tagihan', $bulan->year)
                        ->where('tahun_pelajaran_id', $this->tahunPelajaranId)
                        ->exists();
                    \Log::info('Cek duplikasi:', [
                        'siswa_id' => $siswa->id,
                        'bulan' => $bulan->month,
                        'tahun' => $bulan->year,
                        'tahun_pelajaran_id' => $this->tahunPelajaranId,
                        'exists' => $exists
                    ]);
                    if ($exists) {
                        $bulan->addMonth();
                        continue;
                    }
                    // Buat tagihan
                    $tagihan = Tagihan::create([
                        'user_id' => $this->operatorId,
                        'siswa_id' => $siswa->id,
                        'angkatan' => $siswa->angkatan,
                        'jurusan' => $siswa->jurusan_id,
                        'kelas' => $siswa->kelas,
                        'tahun_pelajaran_id' => $this->tahunPelajaranId,
                        'tanggal_tagihan' => $bulan->format('Y-m-01'),
                        'tanggal_jatuh_tempo' => $bulan->format('Y-m-28'),
                        'keterangan' => $this->keterangan,
                        'denda' => 0,
                    ]);
                    \Log::info('Tagihan dibuat:', [
                        'tagihan_id' => $tagihan->id,
                        'siswa_id' => $siswa->id,
                        'bulan' => $bulan->format('Y-m')
                    ]);
                    // Buat detail tagihan
                    foreach ($biayaList as $biaya) {
                        \Log::info('Cek biaya', [
                            'biaya_id' => $biaya->id,
                            'is_parent' => $biaya->isParent(),
                            'child_count' => $biaya->children->count(),
                            'nama' => $biaya->nama,
                            'jumlah' => $biaya->jumlah
                        ]);
                        if ($biaya->isParent() && $biaya->children->count() > 0) {
                            foreach ($biaya->children as $child) {
                                \Log::info('Buat detail child', [
                                    'tagihan_id' => $tagihan->id,
                                    'biaya_id' => $child->id,
                                    'nama_biaya' => $child->nama,
                                    'jumlah_biaya' => $child->jumlah
                                ]);
                                TagihanDetail::create([
                                    'tagihan_id' => $tagihan->id,
                                    'biaya_id' => $child->id,
                                    'nama_biaya' => $child->nama,
                                    'jumlah_biaya' => $child->jumlah,
                                    'status' => 'baru',
                                ]);
                            }
                        } else {
                            \Log::info('Buat detail single', [
                                'tagihan_id' => $tagihan->id,
                                'biaya_id' => $biaya->id,
                                'nama_biaya' => $biaya->nama,
                                'jumlah_biaya' => $biaya->jumlah
                            ]);
                            TagihanDetail::create([
                                'tagihan_id' => $tagihan->id,
                                'biaya_id' => $biaya->id,
                                'nama_biaya' => $biaya->nama,
                                'jumlah_biaya' => $biaya->jumlah,
                                'status' => 'baru',
                            ]);
                        }
                    }
                    // Kirim notifikasi ke wali untuk setiap tagihan baru
                    if ($this->kirimNotifikasiWali && $siswa->wali) {
                        $siswa->wali->notify(new \App\Notifications\TagihanNotification($tagihan));
                    }
                    $tagihanSiswa[] = $tagihan;
                    $bulan->addMonth();
                }
                // Simpan rekap tagihan untuk notifikasi
                if (count($tagihanSiswa) > 0) {
                    $rekapTagihan[] = [
                        'siswa' => $siswa,
                        'tagihan' => $tagihanSiswa
                    ];
                }
            }
            // Notifikasi rekap ke wali
            // (Dihapus: tidak kirim stdClass, hanya notifikasi per tagihan)
            // Notifikasi ke operator (opsional)
            if ($this->kirimNotifikasiOperator && $operator) {
                $operator->notify(new OperatorTagihanRekapNotification([
                    'jumlah_siswa' => count($this->siswaIds),
                    'tanggal_mulai' => $this->tanggalMulai,
                    'tanggal_akhir' => $this->tanggalAkhir,
                    'keterangan' => 'Generate tagihan masal selesai untuk ' . count($this->siswaIds) . ' siswa.'
                ]));
            }
        } catch (\Exception $e) {
            \Log::error('Job gagal: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'siswa_ids' => $this->siswaIds,
                'biaya_ids' => $this->biayaIds,
                'tahun_pelajaran_id' => $this->tahunPelajaranId,
                'tanggal_mulai' => $this->tanggalMulai,
                'tanggal_akhir' => $this->tanggalAkhir,
                'operator_id' => $this->operatorId
            ]);
            throw $e;
        }
    }
}
