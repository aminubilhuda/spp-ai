<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaliController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\BerandaWaliController;
use App\Http\Controllers\BerandaOperatorController;
use App\Http\Controllers\BiayaController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\KwitansiPembayaranController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::prefix('operator')->middleware(['auth','auth.operator'])->group(function(){
    Route::get('/beranda', [BerandaOperatorController::class, 'index'])->name('operator.beranda');
    Route::resource('user', UserController::class);
    Route::resource('wali', WaliController::class);
    
    // Siswa export and import routes
    Route::get('siswa/export', [SiswaController::class, 'export'])->name('siswa.export');
    Route::get('siswa/import/template', [SiswaController::class, 'importTemplate'])->name('siswa.import.template');
    Route::get('siswa/import', [SiswaController::class, 'importForm'])->name('siswa.import.form');
    Route::post('siswa/import', [SiswaController::class, 'importStore'])->name('siswa.import.store');
    
    Route::resource('siswa', SiswaController::class);
    Route::get('siswa/{id}/wali', [SiswaController::class, 'waliDetail'])->name('siswa.wali');
    Route::post('siswa/tambahkewali', [SiswaController::class, 'tambahKeWali'])->name('siswa.tambahkewali');
    Route::post('siswa/hapusdariwall', [SiswaController::class, 'hapusDariWali'])->name('siswa.hapusdariwall');
    Route::resource('jurusan', JurusanController::class);
    Route::resource('biaya', BiayaController::class);    // Payment routes
    Route::get('tagihan/{id}/detail', [TagihanController::class, 'detail'])->name('tagihan.detail');
    Route::post('pembayaran/store', [PembayaranController::class, 'store'])->name('pembayaran.store');
    Route::get('tagihan/siswa/{siswaId}', [TagihanController::class, 'showByStudent'])->name('tagihan.showByStudent');
    Route::delete('tagihan-kategori', [TagihanController::class, 'deleteByCategory'])->name('tagihan.deleteByCategory');
    Route::delete('tagihan-detail/{id}', [TagihanController::class, 'destroyDetail'])->name('tagihan.destroyDetail');
    Route::post('tagihan-detail/{id}/update', [TagihanController::class, 'updateDetail'])->name('tagihan.updateDetail');
    Route::resource('tagihan', TagihanController::class);    Route::get('tagihan-detail/{id}/info', [TagihanController::class, 'detailInfo'])->name('tagihan.detailInfo');
    Route::put('tagihan-detail/{id}/update', [TagihanController::class, 'updateDetail'])->name('tagihan.updateDetail');

    Route::get('kwitansi-pembayaran/{id}', [KwitansiPembayaranController::class, 'show'])->name('kwitansi_pembayaran.show');
});

Route::prefix('walimurid')->middleware(['auth','auth.wali'])->group(function(){
    Route::get('/beranda', [BerandaWaliController::class, 'index'])->name('wali.beranda');
});

Route::prefix('admin')->middleware(['auth','auth.admin'])->group(function(){
    Route::get('tagihan-detail/{id}/info', [TagihanController::class, 'detailInfo'])->name('tagihan.detailInfo');
});

Route::get('logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');
// video ke 14 sekarang