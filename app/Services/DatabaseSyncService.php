<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TagihanDetail;
use App\Models\Siswa;
use App\Models\PengeluaranKas;
use App\Models\User;
use App\Models\Setting;
use App\Models\TahunPelajaran;
use App\Models\Jurusan;
use App\Models\Biaya;
use App\Models\BankSekolah;
use App\Models\Bank;
use App\Models\InstansiSetting;
use Carbon\Carbon;

class DatabaseSyncService
{
    protected $onlineApiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->onlineApiUrl = config('app.online_api_url');
        $this->apiKey = config('app.sync_api_key');
    }

    /**
     * Sinkronisasi data dari local ke online
     */
    public function syncToOnline()
    {
        try {
            // Cek koneksi internet
            if (!$this->checkInternetConnection()) {
                Log::info('Tidak ada koneksi internet, sinkronisasi ditunda');
                return false;
            }

            $syncResults = [];

            // Sync Master Data
            $syncResults['users'] = $this->syncUserToOnline();
            $syncResults['settings'] = $this->syncSettingToOnline();
            $syncResults['tahun_pelajarans'] = $this->syncTahunPelajaranToOnline();
            $syncResults['jurusans'] = $this->syncJurusanToOnline();
            $syncResults['biayas'] = $this->syncBiayaToOnline();
            $syncResults['bank_sekolahs'] = $this->syncBankSekolahToOnline();
            $syncResults['banks'] = $this->syncBankToOnline();
            $syncResults['instansi_settings'] = $this->syncInstansiSettingToOnline();

            // Sync Transaction Data
            $syncResults['pembayarans'] = $this->syncPembayaranToOnline();
            $syncResults['tagihans'] = $this->syncTagihanToOnline();
            $syncResults['tagihan_details'] = $this->syncTagihanDetailToOnline();
            $syncResults['siswas'] = $this->syncSiswaToOnline();
            $syncResults['pengeluaran_kas'] = $this->syncPengeluaranKasToOnline();

            Log::info('Sinkronisasi selesai', $syncResults);
            return true;

        } catch (\Exception $e) {
            Log::error('Error sinkronisasi ke online: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sinkronisasi data dari online ke local
     */
    public function syncFromOnline()
    {
        try {
            if (!$this->checkInternetConnection()) {
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->onlineApiUrl . '/api/sync/data');

            if ($response->successful()) {
                $data = $response->json();
                
                DB::transaction(function () use ($data) {
                    // Sync Master Data dari online
                    if (isset($data['users'])) {
                        $this->syncUserFromOnline($data['users']);
                    }
                    if (isset($data['settings'])) {
                        $this->syncSettingFromOnline($data['settings']);
                    }
                    if (isset($data['tahun_pelajarans'])) {
                        $this->syncTahunPelajaranFromOnline($data['tahun_pelajarans']);
                    }
                    if (isset($data['jurusans'])) {
                        $this->syncJurusanFromOnline($data['jurusans']);
                    }
                    if (isset($data['biayas'])) {
                        $this->syncBiayaFromOnline($data['biayas']);
                    }
                    if (isset($data['bank_sekolahs'])) {
                        $this->syncBankSekolahFromOnline($data['bank_sekolahs']);
                    }
                    if (isset($data['banks'])) {
                        $this->syncBankFromOnline($data['banks']);
                    }
                    if (isset($data['instansi_settings'])) {
                        $this->syncInstansiSettingFromOnline($data['instansi_settings']);
                    }

                    // Sync Transaction Data dari online
                    if (isset($data['pembayarans'])) {
                        $this->syncPembayaranFromOnline($data['pembayarans']);
                    }
                    if (isset($data['tagihans'])) {
                        $this->syncTagihanFromOnline($data['tagihans']);
                    }
                    if (isset($data['tagihan_details'])) {
                        $this->syncTagihanDetailFromOnline($data['tagihan_details']);
                    }
                    if (isset($data['siswas'])) {
                        $this->syncSiswaFromOnline($data['siswas']);
                    }
                    if (isset($data['pengeluaran_kas'])) {
                        $this->syncPengeluaranKasFromOnline($data['pengeluaran_kas']);
                    }
                });

                Log::info('Sinkronisasi dari online berhasil');
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Error sinkronisasi dari online: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================================
    // MASTER DATA SYNC METHODS
    // ============================================================================

    /**
     * Sinkronisasi User ke online
     */
    protected function syncUserToOnline()
    {
        $pendingUsers = User::where('sync_status', 'pending')
            ->where('source_system', 'local')
            ->get();

        $synced = 0;
        foreach ($pendingUsers as $user) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post($this->onlineApiUrl . '/api/sync/user', [
                    'data' => $user->toArray(),
                    'sync_id' => $this->generateSyncId(),
                ]);

                if ($response->successful()) {
                    $user->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_id' => $response->json('sync_id')
                    ]);
                    $synced++;
                } else {
                    $user->update(['sync_status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::error('Error sync user: ' . $e->getMessage());
                $user->update(['sync_status' => 'failed']);
            }
        }

        return $synced;
    }

    /**
     * Sinkronisasi Setting ke online
     */
    protected function syncSettingToOnline()
    {
        $pendingSettings = Setting::where('sync_status', 'pending')
            ->where('source_system', 'local')
            ->get();

        $synced = 0;
        foreach ($pendingSettings as $setting) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post($this->onlineApiUrl . '/api/sync/setting', [
                    'data' => $setting->toArray(),
                    'sync_id' => $this->generateSyncId(),
                ]);

                if ($response->successful()) {
                    $setting->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_id' => $response->json('sync_id')
                    ]);
                    $synced++;
                } else {
                    $setting->update(['sync_status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::error('Error sync setting: ' . $e->getMessage());
                $setting->update(['sync_status' => 'failed']);
            }
        }

        return $synced;
    }

    /**
     * Sinkronisasi Tahun Pelajaran ke online
     */
    protected function syncTahunPelajaranToOnline()
    {
        $pendingTahunPelajarans = TahunPelajaran::where('sync_status', 'pending')
            ->where('source_system', 'local')
            ->get();

        $synced = 0;
        foreach ($pendingTahunPelajarans as $tahunPelajaran) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post($this->onlineApiUrl . '/api/sync/tahun-pelajaran', [
                    'data' => $tahunPelajaran->toArray(),
                    'sync_id' => $this->generateSyncId(),
                ]);

                if ($response->successful()) {
                    $tahunPelajaran->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_id' => $response->json('sync_id')
                    ]);
                    $synced++;
                } else {
                    $tahunPelajaran->update(['sync_status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::error('Error sync tahun pelajaran: ' . $e->getMessage());
                $tahunPelajaran->update(['sync_status' => 'failed']);
            }
        }

        return $synced;
    }

    /**
     * Sinkronisasi Jurusan ke online
     */
    protected function syncJurusanToOnline()
    {
        $pendingJurusans = Jurusan::where('sync_status', 'pending')
            ->where('source_system', 'local')
            ->get();

        $synced = 0;
        foreach ($pendingJurusans as $jurusan) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post($this->onlineApiUrl . '/api/sync/jurusan', [
                    'data' => $jurusan->toArray(),
                    'sync_id' => $this->generateSyncId(),
                ]);

                if ($response->successful()) {
                    $jurusan->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_id' => $response->json('sync_id')
                    ]);
                    $synced++;
                } else {
                    $jurusan->update(['sync_status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::error('Error sync jurusan: ' . $e->getMessage());
                $jurusan->update(['sync_status' => 'failed']);
            }
        }

        return $synced;
    }

    /**
     * Sinkronisasi Biaya ke online
     */
    protected function syncBiayaToOnline()
    {
        $pendingBiayas = Biaya::where('sync_status', 'pending')
            ->where('source_system', 'local')
            ->get();

        $synced = 0;
        foreach ($pendingBiayas as $biaya) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post($this->onlineApiUrl . '/api/sync/biaya', [
                    'data' => $biaya->toArray(),
                    'sync_id' => $this->generateSyncId(),
                ]);

                if ($response->successful()) {
                    $biaya->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_id' => $response->json('sync_id')
                    ]);
                    $synced++;
                } else {
                    $biaya->update(['sync_status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::error('Error sync biaya: ' . $e->getMessage());
                $biaya->update(['sync_status' => 'failed']);
            }
        }

        return $synced;
    }

    /**
     * Sinkronisasi Bank Sekolah ke online
     */
    protected function syncBankSekolahToOnline()
    {
        $pendingBankSekolahs = BankSekolah::where('sync_status', 'pending')
            ->where('source_system', 'local')
            ->get();

        $synced = 0;
        foreach ($pendingBankSekolahs as $bankSekolah) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post($this->onlineApiUrl . '/api/sync/bank-sekolah', [
                    'data' => $bankSekolah->toArray(),
                    'sync_id' => $this->generateSyncId(),
                ]);

                if ($response->successful()) {
                    $bankSekolah->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_id' => $response->json('sync_id')
                    ]);
                    $synced++;
                } else {
                    $bankSekolah->update(['sync_status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::error('Error sync bank sekolah: ' . $e->getMessage());
                $bankSekolah->update(['sync_status' => 'failed']);
            }
        }

        return $synced;
    }

    /**
     * Sinkronisasi Bank ke online
     */
    protected function syncBankToOnline()
    {
        $pendingBanks = Bank::where('sync_status', 'pending')
            ->where('source_system', 'local')
            ->get();

        $synced = 0;
        foreach ($pendingBanks as $bank) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post($this->onlineApiUrl . '/api/sync/bank', [
                    'data' => $bank->toArray(),
                    'sync_id' => $this->generateSyncId(),
                ]);

                if ($response->successful()) {
                    $bank->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_id' => $response->json('sync_id')
                    ]);
                    $synced++;
                } else {
                    $bank->update(['sync_status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::error('Error sync bank: ' . $e->getMessage());
                $bank->update(['sync_status' => 'failed']);
            }
        }

        return $synced;
    }

    /**
     * Sinkronisasi Instansi Setting ke online
     */
    protected function syncInstansiSettingToOnline()
    {
        $pendingInstansiSettings = InstansiSetting::where('sync_status', 'pending')
            ->where('source_system', 'local')
            ->get();

        $synced = 0;
        foreach ($pendingInstansiSettings as $instansiSetting) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post($this->onlineApiUrl . '/api/sync/instansi-setting', [
                    'data' => $instansiSetting->toArray(),
                    'sync_id' => $this->generateSyncId(),
                ]);

                if ($response->successful()) {
                    $instansiSetting->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_id' => $response->json('sync_id')
                    ]);
                    $synced++;
                } else {
                    $instansiSetting->update(['sync_status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::error('Error sync instansi setting: ' . $e->getMessage());
                $instansiSetting->update(['sync_status' => 'failed']);
            }
        }

        return $synced;
    }

    // ============================================================================
    // TRANSACTION DATA SYNC METHODS (EXISTING)
    // ============================================================================

    /**
     * Sinkronisasi Pembayaran ke online
     */
    protected function syncPembayaranToOnline()
    {
        $pendingPembayarans = Pembayaran::where('sync_status', 'pending')
            ->where('source_system', 'local')
            ->get();

        $synced = 0;
        foreach ($pendingPembayarans as $pembayaran) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post($this->onlineApiUrl . '/api/sync/pembayaran', [
                    'data' => $pembayaran->toArray(),
                    'sync_id' => $this->generateSyncId(),
                ]);

                if ($response->successful()) {
                    $pembayaran->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_id' => $response->json('sync_id')
                    ]);
                    $synced++;
                } else {
                    $pembayaran->update(['sync_status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::error('Error sync pembayaran: ' . $e->getMessage());
                $pembayaran->update(['sync_status' => 'failed']);
            }
        }

        return $synced;
    }

    /**
     * Sinkronisasi Tagihan ke online
     */
    protected function syncTagihanToOnline()
    {
        $pendingTagihans = Tagihan::where('sync_status', 'pending')
            ->where('source_system', 'local')
            ->get();

        $synced = 0;
        foreach ($pendingTagihans as $tagihan) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post($this->onlineApiUrl . '/api/sync/tagihan', [
                    'data' => $tagihan->toArray(),
                    'sync_id' => $this->generateSyncId(),
                ]);

                if ($response->successful()) {
                    $tagihan->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_id' => $response->json('sync_id')
                    ]);
                    $synced++;
                } else {
                    $tagihan->update(['sync_status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::error('Error sync tagihan: ' . $e->getMessage());
                $tagihan->update(['sync_status' => 'failed']);
            }
        }

        return $synced;
    }

    /**
     * Sinkronisasi Tagihan Detail ke online
     */
    protected function syncTagihanDetailToOnline()
    {
        $pendingDetails = TagihanDetail::where('sync_status', 'pending')
            ->where('source_system', 'local')
            ->get();

        $synced = 0;
        foreach ($pendingDetails as $detail) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post($this->onlineApiUrl . '/api/sync/tagihan-detail', [
                    'data' => $detail->toArray(),
                    'sync_id' => $this->generateSyncId(),
                ]);

                if ($response->successful()) {
                    $detail->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_id' => $response->json('sync_id')
                    ]);
                    $synced++;
                } else {
                    $detail->update(['sync_status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::error('Error sync tagihan detail: ' . $e->getMessage());
                $detail->update(['sync_status' => 'failed']);
            }
        }

        return $synced;
    }

    /**
     * Sinkronisasi Siswa ke online
     */
    protected function syncSiswaToOnline()
    {
        $pendingSiswas = Siswa::where('sync_status', 'pending')
            ->where('source_system', 'local')
            ->get();

        $synced = 0;
        foreach ($pendingSiswas as $siswa) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post($this->onlineApiUrl . '/api/sync/siswa', [
                    'data' => $siswa->toArray(),
                    'sync_id' => $this->generateSyncId(),
                ]);

                if ($response->successful()) {
                    $siswa->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_id' => $response->json('sync_id')
                    ]);
                    $synced++;
                } else {
                    $siswa->update(['sync_status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::error('Error sync siswa: ' . $e->getMessage());
                $siswa->update(['sync_status' => 'failed']);
            }
        }

        return $synced;
    }

    /**
     * Sinkronisasi Pengeluaran Kas ke online
     */
    protected function syncPengeluaranKasToOnline()
    {
        $pendingPengeluaran = PengeluaranKas::where('sync_status', 'pending')
            ->where('source_system', 'local')
            ->get();

        $synced = 0;
        foreach ($pendingPengeluaran as $pengeluaran) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post($this->onlineApiUrl . '/api/sync/pengeluaran-kas', [
                    'data' => $pengeluaran->toArray(),
                    'sync_id' => $this->generateSyncId(),
                ]);

                if ($response->successful()) {
                    $pengeluaran->update([
                        'sync_status' => 'synced',
                        'synced_at' => now(),
                        'sync_id' => $response->json('sync_id')
                    ]);
                    $synced++;
                } else {
                    $pengeluaran->update(['sync_status' => 'failed']);
                }
            } catch (\Exception $e) {
                Log::error('Error sync pengeluaran kas: ' . $e->getMessage());
                $pengeluaran->update(['sync_status' => 'failed']);
            }
        }

        return $synced;
    }

    // ============================================================================
    // SYNC FROM ONLINE METHODS
    // ============================================================================

    /**
     * Sinkronisasi User dari online
     */
    protected function syncUserFromOnline($onlineUsers)
    {
        foreach ($onlineUsers as $onlineData) {
            $existing = User::where('sync_id', $onlineData['sync_id'])->first();
            
            if (!$existing) {
                $user = new User($onlineData);
                $user->sync_id = $onlineData['sync_id'];
                $user->source_system = 'online';
                $user->sync_status = 'synced';
                $user->synced_at = now();
                $user->save();
            } else {
                $existing->update($onlineData);
            }
        }
    }

    /**
     * Sinkronisasi Setting dari online
     */
    protected function syncSettingFromOnline($onlineSettings)
    {
        foreach ($onlineSettings as $onlineData) {
            $existing = Setting::where('sync_id', $onlineData['sync_id'])->first();
            
            if (!$existing) {
                $setting = new Setting($onlineData);
                $setting->sync_id = $onlineData['sync_id'];
                $setting->source_system = 'online';
                $setting->sync_status = 'synced';
                $setting->synced_at = now();
                $setting->save();
            } else {
                $existing->update($onlineData);
            }
        }
    }

    /**
     * Sinkronisasi Tahun Pelajaran dari online
     */
    protected function syncTahunPelajaranFromOnline($onlineTahunPelajarans)
    {
        foreach ($onlineTahunPelajarans as $onlineData) {
            $existing = TahunPelajaran::where('sync_id', $onlineData['sync_id'])->first();
            
            if (!$existing) {
                $tahunPelajaran = new TahunPelajaran($onlineData);
                $tahunPelajaran->sync_id = $onlineData['sync_id'];
                $tahunPelajaran->source_system = 'online';
                $tahunPelajaran->sync_status = 'synced';
                $tahunPelajaran->synced_at = now();
                $tahunPelajaran->save();
            } else {
                $existing->update($onlineData);
            }
        }
    }

    /**
     * Sinkronisasi Jurusan dari online
     */
    protected function syncJurusanFromOnline($onlineJurusans)
    {
        foreach ($onlineJurusans as $onlineData) {
            $existing = Jurusan::where('sync_id', $onlineData['sync_id'])->first();
            
            if (!$existing) {
                $jurusan = new Jurusan($onlineData);
                $jurusan->sync_id = $onlineData['sync_id'];
                $jurusan->source_system = 'online';
                $jurusan->sync_status = 'synced';
                $jurusan->synced_at = now();
                $jurusan->save();
            } else {
                $existing->update($onlineData);
            }
        }
    }

    /**
     * Sinkronisasi Biaya dari online
     */
    protected function syncBiayaFromOnline($onlineBiayas)
    {
        foreach ($onlineBiayas as $onlineData) {
            $existing = Biaya::where('sync_id', $onlineData['sync_id'])->first();
            
            if (!$existing) {
                $biaya = new Biaya($onlineData);
                $biaya->sync_id = $onlineData['sync_id'];
                $biaya->source_system = 'online';
                $biaya->sync_status = 'synced';
                $biaya->synced_at = now();
                $biaya->save();
            } else {
                $existing->update($onlineData);
            }
        }
    }

    /**
     * Sinkronisasi Bank Sekolah dari online
     */
    protected function syncBankSekolahFromOnline($onlineBankSekolahs)
    {
        foreach ($onlineBankSekolahs as $onlineData) {
            $existing = BankSekolah::where('sync_id', $onlineData['sync_id'])->first();
            
            if (!$existing) {
                $bankSekolah = new BankSekolah($onlineData);
                $bankSekolah->sync_id = $onlineData['sync_id'];
                $bankSekolah->source_system = 'online';
                $bankSekolah->sync_status = 'synced';
                $bankSekolah->synced_at = now();
                $bankSekolah->save();
            } else {
                $existing->update($onlineData);
            }
        }
    }

    /**
     * Sinkronisasi Bank dari online
     */
    protected function syncBankFromOnline($onlineBanks)
    {
        foreach ($onlineBanks as $onlineData) {
            $existing = Bank::where('sync_id', $onlineData['sync_id'])->first();
            
            if (!$existing) {
                $bank = new Bank($onlineData);
                $bank->sync_id = $onlineData['sync_id'];
                $bank->source_system = 'online';
                $bank->sync_status = 'synced';
                $bank->synced_at = now();
                $bank->save();
            } else {
                $existing->update($onlineData);
            }
        }
    }

    /**
     * Sinkronisasi Instansi Setting dari online
     */
    protected function syncInstansiSettingFromOnline($onlineInstansiSettings)
    {
        foreach ($onlineInstansiSettings as $onlineData) {
            $existing = InstansiSetting::where('sync_id', $onlineData['sync_id'])->first();
            
            if (!$existing) {
                $instansiSetting = new InstansiSetting($onlineData);
                $instansiSetting->sync_id = $onlineData['sync_id'];
                $instansiSetting->source_system = 'online';
                $instansiSetting->sync_status = 'synced';
                $instansiSetting->synced_at = now();
                $instansiSetting->save();
            } else {
                $existing->update($onlineData);
            }
        }
    }

    /**
     * Sinkronisasi Pembayaran dari online
     */
    protected function syncPembayaranFromOnline($onlinePembayarans)
    {
        foreach ($onlinePembayarans as $onlineData) {
            $existing = Pembayaran::where('sync_id', $onlineData['sync_id'])->first();
            
            if (!$existing) {
                // Buat record baru
                $pembayaran = new Pembayaran($onlineData);
                $pembayaran->sync_id = $onlineData['sync_id'];
                $pembayaran->source_system = 'online';
                $pembayaran->sync_status = 'synced';
                $pembayaran->synced_at = now();
                $pembayaran->save();
            } else {
                // Update record yang ada
                $existing->update($onlineData);
            }
        }
    }

    /**
     * Sinkronisasi Tagihan dari online
     */
    protected function syncTagihanFromOnline($onlineTagihans)
    {
        foreach ($onlineTagihans as $onlineData) {
            $existing = Tagihan::where('sync_id', $onlineData['sync_id'])->first();
            
            if (!$existing) {
                $tagihan = new Tagihan($onlineData);
                $tagihan->sync_id = $onlineData['sync_id'];
                $tagihan->source_system = 'online';
                $tagihan->sync_status = 'synced';
                $tagihan->synced_at = now();
                $tagihan->save();
            } else {
                $existing->update($onlineData);
            }
        }
    }

    /**
     * Sinkronisasi Tagihan Detail dari online
     */
    protected function syncTagihanDetailFromOnline($onlineDetails)
    {
        foreach ($onlineDetails as $onlineData) {
            $existing = TagihanDetail::where('sync_id', $onlineData['sync_id'])->first();
            
            if (!$existing) {
                $detail = new TagihanDetail($onlineData);
                $detail->sync_id = $onlineData['sync_id'];
                $detail->source_system = 'online';
                $detail->sync_status = 'synced';
                $detail->synced_at = now();
                $detail->save();
            } else {
                $existing->update($onlineData);
            }
        }
    }

    /**
     * Sinkronisasi Siswa dari online
     */
    protected function syncSiswaFromOnline($onlineSiswas)
    {
        foreach ($onlineSiswas as $onlineData) {
            $existing = Siswa::where('sync_id', $onlineData['sync_id'])->first();
            
            if (!$existing) {
                $siswa = new Siswa($onlineData);
                $siswa->sync_id = $onlineData['sync_id'];
                $siswa->source_system = 'online';
                $siswa->sync_status = 'synced';
                $siswa->synced_at = now();
                $siswa->save();
            } else {
                $existing->update($onlineData);
            }
        }
    }

    /**
     * Sinkronisasi Pengeluaran Kas dari online
     */
    protected function syncPengeluaranKasFromOnline($onlinePengeluaran)
    {
        foreach ($onlinePengeluaran as $onlineData) {
            $existing = PengeluaranKas::where('sync_id', $onlineData['sync_id'])->first();
            
            if (!$existing) {
                $pengeluaran = new PengeluaranKas($onlineData);
                $pengeluaran->sync_id = $onlineData['sync_id'];
                $pengeluaran->source_system = 'online';
                $pengeluaran->sync_status = 'synced';
                $pengeluaran->synced_at = now();
                $pengeluaran->save();
            } else {
                $existing->update($onlineData);
            }
        }
    }

    /**
     * Cek koneksi internet (lebih toleran)
     */
    public function checkInternetConnection()
    {
        try {
            // 1. Coba endpoint ipify
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get('https://api.ipify.org?format=json');
            if ($response->successful()) return true;
        } catch (\Exception $e) {
            \Log::warning('Cek koneksi internet gagal (ipify): ' . $e->getMessage());
        }
        try {
            // 2. Coba endpoint httpbin
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get('https://httpbin.org/get');
            if ($response->successful()) return true;
        } catch (\Exception $e) {
            \Log::warning('Cek koneksi internet gagal (httpbin): ' . $e->getMessage());
        }
        try {
            // 3. Coba endpoint google
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get('https://www.google.com');
            if ($response->successful()) return true;
        } catch (\Exception $e) {
            \Log::warning('Cek koneksi internet gagal (google): ' . $e->getMessage());
        }
        return false;
    }

    /**
     * Generate Sync ID unik
     */
    protected function generateSyncId()
    {
        return uniqid('sync_', true) . '_' . time();
    }

    /**
     * Reset status sync untuk retry
     */
    public function resetFailedSync()
    {
        // Master Data
        User::where('sync_status', 'failed')->update(['sync_status' => 'pending']);
        Setting::where('sync_status', 'failed')->update(['sync_status' => 'pending']);
        TahunPelajaran::where('sync_status', 'failed')->update(['sync_status' => 'pending']);
        Jurusan::where('sync_status', 'failed')->update(['sync_status' => 'pending']);
        Biaya::where('sync_status', 'failed')->update(['sync_status' => 'pending']);
        BankSekolah::where('sync_status', 'failed')->update(['sync_status' => 'pending']);
        Bank::where('sync_status', 'failed')->update(['sync_status' => 'pending']);
        InstansiSetting::where('sync_status', 'failed')->update(['sync_status' => 'pending']);

        // Transaction Data
        Pembayaran::where('sync_status', 'failed')->update(['sync_status' => 'pending']);
        Tagihan::where('sync_status', 'failed')->update(['sync_status' => 'pending']);
        TagihanDetail::where('sync_status', 'failed')->update(['sync_status' => 'pending']);
        Siswa::where('sync_status', 'failed')->update(['sync_status' => 'pending']);
        PengeluaranKas::where('sync_status', 'failed')->update(['sync_status' => 'pending']);
    }
} 