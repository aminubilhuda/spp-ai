<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

class SyncController extends Controller
{
    /**
     * Get all data for sync
     */
    public function getSyncData(Request $request)
    {
        try {
            // Validasi API key
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = [
                // Master Data
                'users' => User::where('sync_status', 'pending')
                    ->where('source_system', 'online')
                    ->get(),
                'settings' => Setting::where('sync_status', 'pending')
                    ->where('source_system', 'online')
                    ->get(),
                'tahun_pelajarans' => TahunPelajaran::where('sync_status', 'pending')
                    ->where('source_system', 'online')
                    ->get(),
                'jurusans' => Jurusan::where('sync_status', 'pending')
                    ->where('source_system', 'online')
                    ->get(),
                'biayas' => Biaya::where('sync_status', 'pending')
                    ->where('source_system', 'online')
                    ->get(),
                'bank_sekolahs' => BankSekolah::where('sync_status', 'pending')
                    ->where('source_system', 'online')
                    ->get(),
                'banks' => Bank::where('sync_status', 'pending')
                    ->where('source_system', 'online')
                    ->get(),
                'instansi_settings' => InstansiSetting::where('sync_status', 'pending')
                    ->where('source_system', 'online')
                    ->get(),

                // Transaction Data
                'pembayarans' => Pembayaran::where('sync_status', 'pending')
                    ->where('source_system', 'online')
                    ->get(),
                'tagihans' => Tagihan::where('sync_status', 'pending')
                    ->where('source_system', 'online')
                    ->get(),
                'tagihan_details' => TagihanDetail::where('sync_status', 'pending')
                    ->where('source_system', 'online')
                    ->get(),
                'siswas' => Siswa::where('sync_status', 'pending')
                    ->where('source_system', 'online')
                    ->get(),
                'pengeluaran_kas' => PengeluaranKas::where('sync_status', 'pending')
                    ->where('source_system', 'online')
                    ->get(),
            ];

            return response()->json($data);

        } catch (\Exception $e) {
            Log::error('Error get sync data: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    // ============================================================================
    // MASTER DATA SYNC ENDPOINTS
    // ============================================================================

    /**
     * Sync User
     */
    public function syncUser(Request $request)
    {
        try {
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = $request->input('data');
            $syncId = $request->input('sync_id');

            DB::transaction(function () use ($data, $syncId) {
                $existing = User::where('sync_id', $syncId)->first();
                
                if (!$existing) {
                    $user = new User($data);
                    $user->sync_id = $syncId;
                    $user->source_system = 'online';
                    $user->sync_status = 'synced';
                    $user->synced_at = now();
                    $user->save();
                } else {
                    $existing->update($data);
                }
            });

            return response()->json(['success' => true, 'sync_id' => $syncId]);

        } catch (\Exception $e) {
            Log::error('Error sync user: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Sync Setting
     */
    public function syncSetting(Request $request)
    {
        try {
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = $request->input('data');
            $syncId = $request->input('sync_id');

            DB::transaction(function () use ($data, $syncId) {
                $existing = Setting::where('sync_id', $syncId)->first();
                
                if (!$existing) {
                    $setting = new Setting($data);
                    $setting->sync_id = $syncId;
                    $setting->source_system = 'online';
                    $setting->sync_status = 'synced';
                    $setting->synced_at = now();
                    $setting->save();
                } else {
                    $existing->update($data);
                }
            });

            return response()->json(['success' => true, 'sync_id' => $syncId]);

        } catch (\Exception $e) {
            Log::error('Error sync setting: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Sync Tahun Pelajaran
     */
    public function syncTahunPelajaran(Request $request)
    {
        try {
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = $request->input('data');
            $syncId = $request->input('sync_id');

            DB::transaction(function () use ($data, $syncId) {
                $existing = TahunPelajaran::where('sync_id', $syncId)->first();
                
                if (!$existing) {
                    $tahunPelajaran = new TahunPelajaran($data);
                    $tahunPelajaran->sync_id = $syncId;
                    $tahunPelajaran->source_system = 'online';
                    $tahunPelajaran->sync_status = 'synced';
                    $tahunPelajaran->synced_at = now();
                    $tahunPelajaran->save();
                } else {
                    $existing->update($data);
                }
            });

            return response()->json(['success' => true, 'sync_id' => $syncId]);

        } catch (\Exception $e) {
            Log::error('Error sync tahun pelajaran: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Sync Jurusan
     */
    public function syncJurusan(Request $request)
    {
        try {
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = $request->input('data');
            $syncId = $request->input('sync_id');

            DB::transaction(function () use ($data, $syncId) {
                $existing = Jurusan::where('sync_id', $syncId)->first();
                
                if (!$existing) {
                    $jurusan = new Jurusan($data);
                    $jurusan->sync_id = $syncId;
                    $jurusan->source_system = 'online';
                    $jurusan->sync_status = 'synced';
                    $jurusan->synced_at = now();
                    $jurusan->save();
                } else {
                    $existing->update($data);
                }
            });

            return response()->json(['success' => true, 'sync_id' => $syncId]);

        } catch (\Exception $e) {
            Log::error('Error sync jurusan: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Sync Biaya
     */
    public function syncBiaya(Request $request)
    {
        try {
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = $request->input('data');
            $syncId = $request->input('sync_id');

            DB::transaction(function () use ($data, $syncId) {
                $existing = Biaya::where('sync_id', $syncId)->first();
                
                if (!$existing) {
                    $biaya = new Biaya($data);
                    $biaya->sync_id = $syncId;
                    $biaya->source_system = 'online';
                    $biaya->sync_status = 'synced';
                    $biaya->synced_at = now();
                    $biaya->save();
                } else {
                    $existing->update($data);
                }
            });

            return response()->json(['success' => true, 'sync_id' => $syncId]);

        } catch (\Exception $e) {
            Log::error('Error sync biaya: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Sync Bank Sekolah
     */
    public function syncBankSekolah(Request $request)
    {
        try {
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = $request->input('data');
            $syncId = $request->input('sync_id');

            DB::transaction(function () use ($data, $syncId) {
                $existing = BankSekolah::where('sync_id', $syncId)->first();
                
                if (!$existing) {
                    $bankSekolah = new BankSekolah($data);
                    $bankSekolah->sync_id = $syncId;
                    $bankSekolah->source_system = 'online';
                    $bankSekolah->sync_status = 'synced';
                    $bankSekolah->synced_at = now();
                    $bankSekolah->save();
                } else {
                    $existing->update($data);
                }
            });

            return response()->json(['success' => true, 'sync_id' => $syncId]);

        } catch (\Exception $e) {
            Log::error('Error sync bank sekolah: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Sync Bank
     */
    public function syncBank(Request $request)
    {
        try {
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = $request->input('data');
            $syncId = $request->input('sync_id');

            DB::transaction(function () use ($data, $syncId) {
                $existing = Bank::where('sync_id', $syncId)->first();
                
                if (!$existing) {
                    $bank = new Bank($data);
                    $bank->sync_id = $syncId;
                    $bank->source_system = 'online';
                    $bank->sync_status = 'synced';
                    $bank->synced_at = now();
                    $bank->save();
                } else {
                    $existing->update($data);
                }
            });

            return response()->json(['success' => true, 'sync_id' => $syncId]);

        } catch (\Exception $e) {
            Log::error('Error sync bank: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Sync Instansi Setting
     */
    public function syncInstansiSetting(Request $request)
    {
        try {
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = $request->input('data');
            $syncId = $request->input('sync_id');

            DB::transaction(function () use ($data, $syncId) {
                $existing = InstansiSetting::where('sync_id', $syncId)->first();
                
                if (!$existing) {
                    $instansiSetting = new InstansiSetting($data);
                    $instansiSetting->sync_id = $syncId;
                    $instansiSetting->source_system = 'online';
                    $instansiSetting->sync_status = 'synced';
                    $instansiSetting->synced_at = now();
                    $instansiSetting->save();
                } else {
                    $existing->update($data);
                }
            });

            return response()->json(['success' => true, 'sync_id' => $syncId]);

        } catch (\Exception $e) {
            Log::error('Error sync instansi setting: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    // ============================================================================
    // TRANSACTION DATA SYNC ENDPOINTS (EXISTING)
    // ============================================================================

    /**
     * Sync Pembayaran
     */
    public function syncPembayaran(Request $request)
    {
        try {
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = $request->input('data');
            $syncId = $request->input('sync_id');

            DB::transaction(function () use ($data, $syncId) {
                $existing = Pembayaran::where('sync_id', $syncId)->first();
                
                if (!$existing) {
                    $pembayaran = new Pembayaran($data);
                    $pembayaran->sync_id = $syncId;
                    $pembayaran->source_system = 'online';
                    $pembayaran->sync_status = 'synced';
                    $pembayaran->synced_at = now();
                    $pembayaran->save();
                } else {
                    $existing->update($data);
                }
            });

            return response()->json(['success' => true, 'sync_id' => $syncId]);

        } catch (\Exception $e) {
            Log::error('Error sync pembayaran: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Sync Tagihan
     */
    public function syncTagihan(Request $request)
    {
        try {
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = $request->input('data');
            $syncId = $request->input('sync_id');

            DB::transaction(function () use ($data, $syncId) {
                $existing = Tagihan::where('sync_id', $syncId)->first();
                
                if (!$existing) {
                    $tagihan = new Tagihan($data);
                    $tagihan->sync_id = $syncId;
                    $tagihan->source_system = 'online';
                    $tagihan->sync_status = 'synced';
                    $tagihan->synced_at = now();
                    $tagihan->save();
                } else {
                    $existing->update($data);
                }
            });

            return response()->json(['success' => true, 'sync_id' => $syncId]);

        } catch (\Exception $e) {
            Log::error('Error sync tagihan: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Sync Tagihan Detail
     */
    public function syncTagihanDetail(Request $request)
    {
        try {
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = $request->input('data');
            $syncId = $request->input('sync_id');

            DB::transaction(function () use ($data, $syncId) {
                $existing = TagihanDetail::where('sync_id', $syncId)->first();
                
                if (!$existing) {
                    $detail = new TagihanDetail($data);
                    $detail->sync_id = $syncId;
                    $detail->source_system = 'online';
                    $detail->sync_status = 'synced';
                    $detail->synced_at = now();
                    $detail->save();
                } else {
                    $existing->update($data);
                }
            });

            return response()->json(['success' => true, 'sync_id' => $syncId]);

        } catch (\Exception $e) {
            Log::error('Error sync tagihan detail: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Sync Siswa
     */
    public function syncSiswa(Request $request)
    {
        try {
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = $request->input('data');
            $syncId = $request->input('sync_id');

            DB::transaction(function () use ($data, $syncId) {
                $existing = Siswa::where('sync_id', $syncId)->first();
                
                if (!$existing) {
                    $siswa = new Siswa($data);
                    $siswa->sync_id = $syncId;
                    $siswa->source_system = 'online';
                    $siswa->sync_status = 'synced';
                    $siswa->synced_at = now();
                    $siswa->save();
                } else {
                    $existing->update($data);
                }
            });

            return response()->json(['success' => true, 'sync_id' => $syncId]);

        } catch (\Exception $e) {
            Log::error('Error sync siswa: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Sync Pengeluaran Kas
     */
    public function syncPengeluaranKas(Request $request)
    {
        try {
            if (!$this->validateApiKey($request)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = $request->input('data');
            $syncId = $request->input('sync_id');

            DB::transaction(function () use ($data, $syncId) {
                $existing = PengeluaranKas::where('sync_id', $syncId)->first();
                
                if (!$existing) {
                    $pengeluaran = new PengeluaranKas($data);
                    $pengeluaran->sync_id = $syncId;
                    $pengeluaran->source_system = 'online';
                    $pengeluaran->sync_status = 'synced';
                    $pengeluaran->synced_at = now();
                    $pengeluaran->save();
                } else {
                    $existing->update($data);
                }
            });

            return response()->json(['success' => true, 'sync_id' => $syncId]);

        } catch (\Exception $e) {
            Log::error('Error sync pengeluaran kas: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Validate API Key
     */
    protected function validateApiKey(Request $request)
    {
        $apiKey = $request->header('Authorization');
        $expectedKey = 'Bearer ' . config('app.sync_api_key');
        
        return $apiKey === $expectedKey;
    }
} 