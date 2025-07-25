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
        \Log::info('GenerateTagihanMasalJob started.', [
            'siswa_count' => count($this->siswaIds),
            'biaya_count' => count($this->biayaIds),
            'operator_id' => $this->operatorId
        ]);

        try {
            $siswaList = Siswa::with(['wali'])->whereIn('id', $this->siswaIds)->get();
            $biayaList = Biaya::with('children')->whereIn('id', $this->biayaIds)->get();
            $operator = User::find($this->operatorId);

            // --- OPTIMIZATION: Fetch existing tagihans in one query ---
            $existingTagihans = Tagihan::whereIn('siswa_id', $this->siswaIds)
                ->where('tahun_pelajaran_id', $this->tahunPelajaranId)
                ->whereDate('tanggal_tagihan', '>=', $this->tanggalMulai)
                ->whereDate('tanggal_tagihan', '<=', $this->tanggalAkhir)
                ->select('siswa_id', 'tanggal_tagihan')
                ->get()
                ->mapToGroups(function ($item) {
                    return [$item->siswa_id => Carbon::parse($item->tanggal_tagihan)->format('Y-m')];
                })
                ->map(function ($item) {
                    return $item->unique();
                });
            // --- END OPTIMIZATION ---

            $totalTagihanDibuat = 0;

            foreach ($siswaList as $siswa) {
                \Log::info("Processing siswa: {$siswa->id} - {$siswa->nama}");
                $bulan = Carbon::parse($this->tanggalMulai)->copy();
                $end = Carbon::parse($this->tanggalAkhir)->copy();

                while ($bulan->lte($end)) {
                    $bulanKey = $bulan->format('Y-m');

                    // --- OPTIMIZED DUPLICATE CHECK ---
                    if (isset($existingTagihans[$siswa->id]) && $existingTagihans[$siswa->id]->contains($bulanKey)) {
                        \Log::info("Skipping duplicate tagihan for siswa {$siswa->id} in {$bulanKey}");
                        $bulan->addMonth();
                        continue;
                    }

                    \DB::transaction(function () use ($siswa, $biayaList, $bulan, &$totalTagihanDibuat) {
                        // Buat tagihan
                        $tagihan = Tagihan::create([
                            'user_id' => $this->operatorId,
                            'siswa_id' => $siswa->id,
                            'angkatan' => $siswa->angkatan,
                            'jurusan' => $siswa->jurusan_id,
                            'kelas' => $siswa->kelas,
                            'tahun_pelajaran_id' => $this->tahunPelajaranId,
                            'tanggal_tagihan' => $bulan->copy()->startOfMonth(),
                            'tanggal_jatuh_tempo' => $bulan->copy()->endOfMonth(),
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

                        // --- SIMPLIFIED NOTIFICATION ---
                        if ($this->kirimNotifikasiWali && $siswa->wali) {
                            try {
                                $siswa->wali->notify(new \App\Notifications\TagihanNotification($tagihan));
                                \Log::info("Successfully sent TagihanNotification to wali for siswa_id: {$siswa->id}, tagihan_id: {$tagihan->id}");
                            } catch (\Exception $e) {
                                \Log::error("Failed to send TagihanNotification for siswa_id: {$siswa->id}", ['error' => $e->getMessage()]);
                            }
                        }
                        $totalTagihanDibuat++;
                    });

                    $bulan->addMonth();
                }
            }

            // Notifikasi ke operator (opsional)
            if ($this->kirimNotifikasiOperator && $operator) {
                $operator->notify(new OperatorTagihanRekapNotification([
                    'jumlah_siswa' => count($this->siswaIds),
                    'jumlah_tagihan' => $totalTagihanDibuat,
                    'tanggal_mulai' => $this->tanggalMulai,
                    'tanggal_akhir' => $this->tanggalAkhir,
                    'keterangan' => "Generate tagihan masal selesai. Berhasil membuat {$totalTagihanDibuat} tagihan untuk " . count($this->siswaIds) . ' siswa.'
                ]));
            }

            \Log::info('GenerateTagihanMasalJob finished successfully.', ['total_created' => $totalTagihanDibuat]);

        } catch (\Exception $e) {
            \Log::error('GenerateTagihanMasalJob failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 2000) // Limit trace to prevent huge logs
            ]);
            // Re-throw the exception to let the queue worker handle the failure (e.g., move to failed_jobs table)
            throw $e;
        }
    }
}

