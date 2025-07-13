<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SyncController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Sync API Routes
Route::prefix('sync')->group(function () {
    Route::get('/data', [SyncController::class, 'getSyncData']);
    
    // Master Data Sync Routes
    Route::post('/user', [SyncController::class, 'syncUser']);
    Route::post('/setting', [SyncController::class, 'syncSetting']);
    Route::post('/tahun-pelajaran', [SyncController::class, 'syncTahunPelajaran']);
    Route::post('/jurusan', [SyncController::class, 'syncJurusan']);
    Route::post('/biaya', [SyncController::class, 'syncBiaya']);
    Route::post('/bank-sekolah', [SyncController::class, 'syncBankSekolah']);
    Route::post('/bank', [SyncController::class, 'syncBank']);
    Route::post('/instansi-setting', [SyncController::class, 'syncInstansiSetting']);
    
    // Transaction Data Sync Routes
    Route::post('/pembayaran', [SyncController::class, 'syncPembayaran']);
    Route::post('/tagihan', [SyncController::class, 'syncTagihan']);
    Route::post('/tagihan-detail', [SyncController::class, 'syncTagihanDetail']);
    Route::post('/siswa', [SyncController::class, 'syncSiswa']);
    Route::post('/pengeluaran-kas', [SyncController::class, 'syncPengeluaranKas']);
}); 