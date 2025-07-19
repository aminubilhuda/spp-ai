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

        try {
            $siswaList = Siswa::with(['wali'])->whereIn('id', $this->siswaIds)->get();
            $biayaList = Biaya::with('children')->whereIn('id', $this->biayaIds)->get();

            $rekapTagihan = [];
            $operator = User::find($this->operatorId);

            foreach ($siswaList as $siswa) {
                $tagihanSiswa = [];
                $bulan = Carbon::parse($this->tanggalMulai)->copy();
                $end = Carbon::parse($this->tanggalAkhir)->copy();
                while ($bulan->lte($end)) {

                    // Cek duplikasi tagihan
                    $exists = Tagihan::where('siswa_id', $siswa->id)
                        ->whereMonth('tanggal_tagihan', $bulan->month)
                        ->whereYear('tanggal_tagihan', $bulan->year)
                        ->where('tahun_pelajaran_id', $this->tahunPelajaranId)
                        ->exists();

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

                    // Buat detail tagihan
                    foreach ($biayaList as $biaya) {

                        if ($biaya->isParent() && $biaya->children->count() > 0) {
                            foreach ($biaya->children as $child) {

                                TagihanDetail::create([
                                    'tagihan_id' => $tagihan->id,
                                    'biaya_id' => $child->id,
                                    'nama_biaya' => $child->nama,
                                    'jumlah_biaya' => $child->jumlah,
                                    'status' => 'baru',
                                ]);
                            }
                        } else {

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
                    if ($this->kirimNotifikasiWali && $siswa->wali && !empty($siswa->wali->id)) {
                        \Log::info('DEBUG: Jenis $tagihan sebelum konversi', [
                            'class' => is_object($tagihan) ? get_class($tagihan) : gettype($tagihan),
                            'id' => is_object($tagihan) && isset($tagihan->id) ? $tagihan->id : null
                        ]);
                        $tagihanId = null;
                        if (is_object($tagihan) && isset($tagihan->id)) {
                            $tagihanId = $tagihan->id;
                        } elseif (is_array($tagihan) && isset($tagihan['id'])) {
                            $tagihanId = $tagihan['id'];
                        }
                        if ($tagihanId) {
                            $tagihanModel = \App\Models\Tagihan::where('id', $tagihanId)->first();
                            if ($tagihanModel && $tagihanModel instanceof \Illuminate\Database\Eloquent\Model) {
                                \Log::info('DEBUG: Akan kirim notifikasi wali', [
                                    'tagihan_id' => $tagihanModel->id,
                                    'class' => get_class($tagihanModel)
                                ]);
                                $siswa->wali->notify(new \App\Notifications\TagihanNotification($tagihanModel));
                            } else {
                                \Log::warning('DEBUG: tagihanModel bukan Eloquent', [
                                    'tagihan_id' => $tagihanId,
                                    'class' => $tagihanModel ? get_class($tagihanModel) : 'null'
                                ]);
                            }
                        } else {
                            \Log::warning('DEBUG: Tidak dapat id tagihan untuk notifikasi wali');
                        }
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
                // 'trace' => $e->getTraceAsString(),
                // 'siswa_ids' => $this->siswaIds,
                // 'biaya_ids' => $this->biayaIds,
                // 'tahun_pelajaran_id' => $this->tahunPelajaranId,
                // 'tanggal_mulai' => $this->tanggalMulai,
                // 'tanggal_akhir' => $this->tanggalAkhir,
                'operator_id' => $this->operatorId
            ]);
            throw $e;
        }
    }
}

