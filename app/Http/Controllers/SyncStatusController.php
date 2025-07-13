<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\DatabaseSyncService;
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

class SyncStatusController extends Controller
{
    protected $syncService;

    public function __construct(DatabaseSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Tampilkan halaman status sinkronisasi
     */
    public function index()
    {
        return view('operator.sync_status');
    }

    /**
     * Get status sinkronisasi
     */
    public function getStatus()
    {
        try {
            $stats = $this->getSyncStats();
            $details = $this->getSyncDetails();
            $log = $this->getSyncLog();

            return response()->json([
                'stats' => $stats,
                'details' => $details,
                'log' => $log
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check internet connection
     */
    public function checkConnection()
    {
        try {
            $connected = $this->syncService->checkInternetConnection();
            
            return response()->json([
                'connected' => $connected,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Manual sync
     */
    public function manualSync()
    {
        try {
            $result = $this->syncService->syncToOnline();
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sinkronisasi berhasil dilakukan'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Sinkronisasi gagal dilakukan'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync specific table
     */
    public function syncTable(Request $request, $tableName)
    {
        try {
            $result = false;
            
            switch ($tableName) {
                // Master Data
                case 'users':
                    $result = $this->syncService->syncUserToOnline();
                    break;
                case 'settings':
                    $result = $this->syncService->syncSettingToOnline();
                    break;
                case 'tahun_pelajarans':
                    $result = $this->syncService->syncTahunPelajaranToOnline();
                    break;
                case 'jurusans':
                    $result = $this->syncService->syncJurusanToOnline();
                    break;
                case 'biayas':
                    $result = $this->syncService->syncBiayaToOnline();
                    break;
                case 'bank_sekolahs':
                    $result = $this->syncService->syncBankSekolahToOnline();
                    break;
                case 'banks':
                    $result = $this->syncService->syncBankToOnline();
                    break;
                case 'instansi_settings':
                    $result = $this->syncService->syncInstansiSettingToOnline();
                    break;

                // Transaction Data
                case 'pembayarans':
                    $result = $this->syncService->syncPembayaranToOnline();
                    break;
                case 'tagihans':
                    $result = $this->syncService->syncTagihanToOnline();
                    break;
                case 'tagihan_details':
                    $result = $this->syncService->syncTagihanDetailToOnline();
                    break;
                case 'siswas':
                    $result = $this->syncService->syncSiswaToOnline();
                    break;
                case 'pengeluaran_kas':
                    $result = $this->syncService->syncPengeluaranKasToOnline();
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Tabel tidak valid'
                    ], 400);
            }

            if ($result !== false) {
                return response()->json([
                    'success' => true,
                    'message' => "Sinkronisasi tabel {$tableName} berhasil: {$result} record",
                    'synced_count' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Sinkronisasi tabel {$tableName} gagal"
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset failed sync
     */
    public function resetFailed()
    {
        try {
            $this->syncService->resetFailedSync();
            
            return response()->json([
                'success' => true,
                'message' => 'Reset status sync berhasil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sync statistics
     */
    protected function getSyncStats()
    {
        $stats = [];

        // Master Data Stats
        $stats['users'] = [
            'total' => User::count(),
            'pending' => User::where('sync_status', 'pending')->count(),
            'synced' => User::where('sync_status', 'synced')->count(),
            'failed' => User::where('sync_status', 'failed')->count(),
        ];

        $stats['settings'] = [
            'total' => Setting::count(),
            'pending' => Setting::where('sync_status', 'pending')->count(),
            'synced' => Setting::where('sync_status', 'synced')->count(),
            'failed' => Setting::where('sync_status', 'failed')->count(),
        ];

        $stats['tahun_pelajarans'] = [
            'total' => TahunPelajaran::count(),
            'pending' => TahunPelajaran::where('sync_status', 'pending')->count(),
            'synced' => TahunPelajaran::where('sync_status', 'synced')->count(),
            'failed' => TahunPelajaran::where('sync_status', 'failed')->count(),
        ];

        $stats['jurusans'] = [
            'total' => Jurusan::count(),
            'pending' => Jurusan::where('sync_status', 'pending')->count(),
            'synced' => Jurusan::where('sync_status', 'synced')->count(),
            'failed' => Jurusan::where('sync_status', 'failed')->count(),
        ];

        $stats['biayas'] = [
            'total' => Biaya::count(),
            'pending' => Biaya::where('sync_status', 'pending')->count(),
            'synced' => Biaya::where('sync_status', 'synced')->count(),
            'failed' => Biaya::where('sync_status', 'failed')->count(),
        ];

        $stats['bank_sekolahs'] = [
            'total' => BankSekolah::count(),
            'pending' => BankSekolah::where('sync_status', 'pending')->count(),
            'synced' => BankSekolah::where('sync_status', 'synced')->count(),
            'failed' => BankSekolah::where('sync_status', 'failed')->count(),
        ];

        $stats['banks'] = [
            'total' => Bank::count(),
            'pending' => Bank::where('sync_status', 'pending')->count(),
            'synced' => Bank::where('sync_status', 'synced')->count(),
            'failed' => Bank::where('sync_status', 'failed')->count(),
        ];

        $stats['instansi_settings'] = [
            'total' => InstansiSetting::count(),
            'pending' => InstansiSetting::where('sync_status', 'pending')->count(),
            'synced' => InstansiSetting::where('sync_status', 'synced')->count(),
            'failed' => InstansiSetting::where('sync_status', 'failed')->count(),
        ];

        // Transaction Data Stats
        $stats['pembayarans'] = [
            'total' => Pembayaran::count(),
            'pending' => Pembayaran::where('sync_status', 'pending')->count(),
            'synced' => Pembayaran::where('sync_status', 'synced')->count(),
            'failed' => Pembayaran::where('sync_status', 'failed')->count(),
        ];

        $stats['tagihans'] = [
            'total' => Tagihan::count(),
            'pending' => Tagihan::where('sync_status', 'pending')->count(),
            'synced' => Tagihan::where('sync_status', 'synced')->count(),
            'failed' => Tagihan::where('sync_status', 'failed')->count(),
        ];

        $stats['tagihan_details'] = [
            'total' => TagihanDetail::count(),
            'pending' => TagihanDetail::where('sync_status', 'pending')->count(),
            'synced' => TagihanDetail::where('sync_status', 'synced')->count(),
            'failed' => TagihanDetail::where('sync_status', 'failed')->count(),
        ];

        $stats['siswas'] = [
            'total' => Siswa::count(),
            'pending' => Siswa::where('sync_status', 'pending')->count(),
            'synced' => Siswa::where('sync_status', 'synced')->count(),
            'failed' => Siswa::where('sync_status', 'failed')->count(),
        ];

        $stats['pengeluaran_kas'] = [
            'total' => PengeluaranKas::count(),
            'pending' => PengeluaranKas::where('sync_status', 'pending')->count(),
            'synced' => PengeluaranKas::where('sync_status', 'synced')->count(),
            'failed' => PengeluaranKas::where('sync_status', 'failed')->count(),
        ];

        return $stats;
    }

    /**
     * Get sync details
     */
    protected function getSyncDetails()
    {
        $details = [];

        // Master Data Details
        $details['users'] = User::where('sync_status', '!=', 'synced')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'email', 'sync_status', 'synced_at', 'updated_at']);

        $details['settings'] = Setting::where('sync_status', '!=', 'synced')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'val', 'sync_status', 'synced_at', 'updated_at']);

        $details['tahun_pelajarans'] = TahunPelajaran::where('sync_status', '!=', 'synced')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'nama', 'is_aktif', 'sync_status', 'synced_at', 'updated_at']);

        $details['jurusans'] = Jurusan::where('sync_status', '!=', 'synced')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'nama', 'keterangan', 'sync_status', 'synced_at', 'updated_at']);

        $details['biayas'] = Biaya::where('sync_status', '!=', 'synced')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'nama', 'jumlah', 'sync_status', 'synced_at', 'updated_at']);

        $details['bank_sekolahs'] = BankSekolah::where('sync_status', '!=', 'synced')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'nama_bank', 'no_rekening', 'sync_status', 'synced_at', 'updated_at']);

        $details['banks'] = Bank::where('sync_status', '!=', 'synced')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'nama_bank', 'sandi_bank', 'sync_status', 'synced_at', 'updated_at']);

        $details['instansi_settings'] = InstansiSetting::where('sync_status', '!=', 'synced')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'nama_instansi', 'email_instansi', 'sync_status', 'synced_at', 'updated_at']);

        // Transaction Data Details
        $details['pembayarans'] = Pembayaran::where('sync_status', '!=', 'synced')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'jumlah_dibayar', 'metode_pembayaran', 'sync_status', 'synced_at', 'updated_at']);

        $details['tagihans'] = Tagihan::where('sync_status', '!=', 'synced')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'tanggal_tagihan', 'tanggal_jatuh_tempo', 'sync_status', 'synced_at', 'updated_at']);

        $details['tagihan_details'] = TagihanDetail::where('sync_status', '!=', 'synced')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'nama_biaya', 'jumlah_biaya', 'sync_status', 'synced_at', 'updated_at']);

        $details['siswas'] = Siswa::where('sync_status', '!=', 'synced')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'nama', 'nisn', 'sync_status', 'synced_at', 'updated_at']);

        $details['pengeluaran_kas'] = PengeluaranKas::where('sync_status', '!=', 'synced')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'tanggal', 'jumlah', 'kategori', 'sync_status', 'synced_at', 'updated_at']);

        return $details;
    }

    /**
     * Get sync log
     */
    protected function getSyncLog()
    {
        // Ambil log dari file storage/logs/laravel.log
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return [];
        }

        $logContent = file_get_contents($logFile);
        $lines = explode("\n", $logContent);
        
        // Filter log yang berkaitan dengan sinkronisasi
        $syncLogs = [];
        foreach ($lines as $line) {
            if (strpos($line, 'sync') !== false || strpos($line, 'Sync') !== false) {
                $syncLogs[] = $line;
            }
        }

        // Ambil 50 log terakhir
        return array_slice(array_reverse($syncLogs), 0, 50);
    }
} 