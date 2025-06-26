<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaliController;
use App\Http\Controllers\BiayaController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\BankSekolahController;
use App\Http\Controllers\BerandaWaliController;
use App\Http\Controllers\TagihanRekapController;
use App\Http\Controllers\WaliMuridSiswaController;
use App\Http\Controllers\BerandaOperatorController;
use App\Http\Controllers\WaliMuridTagihanController;
use App\Http\Controllers\WaliMuridPembayaranController;
use App\Http\Controllers\KwitansiPembayaranController;

// ============================================================================
// PUBLIC ROUTES
// ============================================================================

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Login routes wali
Route::get('login-wali', [LoginController::class, 'showLoginFormWali'])->name('login.wali');

// ============================================================================
// OPERATOR ROUTES
// ============================================================================

Route::prefix('operator')->middleware(['auth', 'auth.operator'])->group(function () {
    // Beranda
    Route::get('/beranda', [BerandaOperatorController::class, 'index'])->name('operator.beranda');
    
    // Master Data - Users & Wali
    Route::resource('user', UserController::class);
    Route::resource('wali', WaliController::class);
    Route::resource('bank-sekolah', BankSekolahController::class);
    
    // Master Data - Siswa
    Route::controller(SiswaController::class)->group(function () {
        Route::get('siswa/export', 'export')->name('siswa.export');
        Route::get('siswa/import/template', 'importTemplate')->name('siswa.import.template');
        Route::get('siswa/import', 'importForm')->name('siswa.import.form');
        Route::post('siswa/import', 'importStore')->name('siswa.import.store');
        Route::get('siswa/{id}/wali', 'waliDetail')->name('siswa.wali');
        Route::post('siswa/tambahkewali', 'tambahKeWali')->name('siswa.tambahkewali');
        Route::post('siswa/hapusdariwall', 'hapusDariWali')->name('siswa.hapusdariwall');
    });
    Route::resource('siswa', SiswaController::class);
    
    // Master Data - Jurusan & Biaya
    Route::resources([
        'jurusan' => JurusanController::class,
        'biaya' => BiayaController::class,
    ]);
    
    // Tagihan Management
    Route::controller(TagihanController::class)->group(function () {
        Route::get('tagihan/siswa/{siswaId}', 'showByStudent')->name('tagihan.showByStudent');
        Route::get('tagihan/{id}/detail', 'detail')->name('tagihan.detail');
        Route::get('tagihan-detail/{id}/info', 'detailInfo')->name('tagihan.detailInfo');
        Route::put('tagihan-detail/{id}/update', 'updateDetail')->name('tagihan.updateDetail');
        Route::delete('tagihan-kategori', 'deleteByCategory')->name('tagihan.deleteByCategory');
        Route::delete('tagihan-detail/{id}', 'destroyDetail')->name('tagihan.destroyDetail');
    });
    Route::resource('tagihan', TagihanController::class);
    
    // Pembayaran Management
    Route::controller(PembayaranController::class)->group(function () {
        Route::get('pembayaran', 'index')->name('pembayaran.index');
        Route::post('pembayaran/store', 'store')->name('pembayaran.store');
        Route::post('pembayaran/{id}/confirm', 'confirm')->name('pembayaran.confirm');
    });
    
    // Reports & Kwitansi
    Route::get('tagihan-rekap/{siswa_id}', [TagihanRekapController::class, 'show'])->name('tagihan.rekap');
    Route::get('kwitansi-pembayaran/{id}', [KwitansiPembayaranController::class, 'show'])->name('kwitansi_pembayaran.show');
});

// ============================================================================
// WALI MURID ROUTES
// ============================================================================

Route::prefix('walimurid')->middleware(['auth', 'auth.wali'])->name('wali.')->group(function () {
    // Beranda
    Route::get('/beranda', [BerandaWaliController::class, 'index'])->name('beranda');
    
    // Data Siswa
    Route::resource('siswa', WaliMuridSiswaController::class);
    
    // Tagihan Management
    Route::controller(WaliMuridTagihanController::class)->group(function () {
        Route::get('tagihan/{id}/details', 'getDetails')->name('tagihan.details');
    });
    Route::resource('tagihan', WaliMuridTagihanController::class);
    
    // Pembayaran Management - Menggunakan WaliMuridPembayaranController
    Route::controller(WaliMuridPembayaranController::class)->group(function () {
        Route::get('pembayaran', 'index')->name('pembayaran.index');
        Route::post('pembayaran/store', 'store')->name('pembayaran.store');
        Route::get('pembayaran/{id}', 'show')->name('pembayaran.show');
        Route::get('tagihan/{tagihanId}/details', 'getTagihanDetails')->name('tagihan.details');
    });
    Route::resource('pembayaran', WaliMuridPembayaranController::class)->except(['index', 'store', 'show']);
    
    // Kwitansi
    Route::get('kwitansi-pembayaran/{id}', [KwitansiPembayaranController::class, 'show'])->name('kwitansi_pembayaran.show');
});

// ============================================================================
// ADMIN ROUTES (Additional admin routes)
// ============================================================================

Route::prefix('admin')->middleware(['auth', 'auth.admin'])->group(function () {
    Route::get('tagihan-detail/{id}/info', [TagihanController::class, 'detailInfo'])->name('tagihan.detailInfo');
});

// ============================================================================
// AUTHENTICATION ROUTES
// ============================================================================

Route::get('logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');