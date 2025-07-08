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
use App\Http\Controllers\BankController;
use App\Http\Controllers\BerandaWaliController;
use App\Http\Controllers\TagihanRekapController;
use App\Http\Controllers\WaliMuridSiswaController;
use App\Http\Controllers\BerandaOperatorController;
use App\Http\Controllers\WaliMuridTagihanController;
use App\Http\Controllers\WaliMuridPembayaranController;
use App\Http\Controllers\KwitansiPembayaranController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PanduanPembayaranController;
use App\Http\Controllers\WaliMuridInvoiceController;
use App\Http\Controllers\KartuSppController;
use App\Http\Controllers\LaporanFormController;
use App\Http\Controllers\TahunPelajaranController;

// ============================================================================
// PUBLIC ROUTES
// ============================================================================

// Route untuk login dengan signed URL - Production Ready

// Route untuk login dengan signed URL
Route::get('login/login-url', [LoginController::class, 'loginUrl'])->name('login.url');

Route::get('/', function () {
    return view('landing_page');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

//Panduan Pembayaran
Route::get('panduan-pembayaran/{id}', [PanduanPembayaranController::class, 'index'])->name('panduan.pembayaran');


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
    Route::get('wali/{id}/reset-password', [WaliController::class, 'resetPassword'])->name('wali.reset-password');
    Route::get('wali/{id}/siswa', [WaliController::class, 'siswa'])->name('wali.siswa');
    Route::resource('bank-sekolah', BankSekolahController::class);
    
    // Bank Management
    Route::controller(BankController::class)->group(function () {
        Route::post('bank', 'store')->name('bank.store');
        Route::get('bank', 'getBanks')->name('bank.getBanks');
    });
    
    // Master Data - Siswa
    Route::controller(SiswaController::class)->group(function () {
        Route::get('siswa/search', 'search')->name('siswa.search');
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
        Route::post('pembayaran/confirm/{id}', 'confirm')->name('pembayaran.confirm');
        Route::delete('pembayaran/{id}', 'destroy')->name('pembayaran.destroy');
    });
    Route::resource('pembayaran', PembayaranController::class);
    
    // Reports & Kwitansi
    Route::get('tagihan-rekap/{siswa_id}', [TagihanRekapController::class, 'show'])->name('tagihan.rekap');
    Route::get('kartu-spp/{siswa_id}', [KartuSppController::class, 'show'])->name('kartu.spp');
    Route::get('kwitansi/{id}', [KwitansiPembayaranController::class, 'show'])->name('kwitansi.show');
    Route::post('kwitansi/batch', [KwitansiPembayaranController::class, 'showBatch'])->name('kwitansi.showBatch');
    Route::get('kwitansi/batch/pdf', [KwitansiPembayaranController::class, 'showBatch'])->name('kwitansi.showBatch.pdf');
    
    // Notifications
    Route::controller(NotificationController::class)->group(function () {
        Route::get('notifications', 'index')->name('notifications.index');
        Route::post('notifications/{id}/mark-as-read', 'markAsRead')->name('notifications.markAsRead');
        Route::post('notifications/mark-all-as-read', 'markAllAsRead')->name('notifications.markAllAsRead');
        Route::get('notifications/unread-count', 'unreadCount')->name('notifications.unreadCount');
    });

    //Biaya
    Route::controller(BiayaController::class)->group(function () {
        Route::get('delete-biaya-item/{id}', [BiayaController::class, 'deleteItem'])->name('delete-biaya.item');
    });

    //Status
    Route::controller(StatusController::class)->group(function () {
        Route::get('status/update/{id}', [StatusController::class, 'update'])->name('status.update');
    });

    //Setting
    Route::controller(SettingController::class)->group(function () {
        Route::get('setting', 'index')->name('setting.index');
        Route::post('setting', 'store')->name('setting.store');
    });

    // WhatsApp Settings
    Route::get('whatsapp/settings', [WhatsappController::class, 'settings'])->name('whatsapp.settings');
    Route::post('whatsapp/settings', [WhatsappController::class, 'updateSettings'])->name('whatsapp.update-settings');
    Route::post('whatsapp/test', [WhatsappController::class, 'test'])->name('whatsapp.test');
    Route::post('whatsapp/send-signed-url/{pembayaranId}/{waliId}', [WhatsappController::class, 'sendPembayaranSignedUrl'])->name('whatsapp.send-signed-url');

    Route::get('tagihan/{siswa}/rekap-pdf', [TagihanController::class, 'rekapTagihanPdf'])->name('tagihan.rekap.pdf');

    //Laporan
    Route::get('laporanform/create', [LaporanFormController::class, 'create'])->name('laporanform.create');

    // Tahun Pelajaran
    Route::resource('tahun-pelajaran', TahunPelajaranController::class);
    Route::get('tahun-pelajaran/{id}/set-aktif', [TahunPelajaranController::class, 'setAktif'])->name('tahun-pelajaran.set-aktif');
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
    
    // Invoice
    Route::get('invoice/{id}', [WaliMuridInvoiceController::class, 'show'])->name('invoice.show');
    Route::get('invoice/{id}/html', [WaliMuridInvoiceController::class, 'show'])->name('invoice.show.html');
    
    // Pembayaran Management
    Route::controller(WaliMuridPembayaranController::class)->group(function () {
        Route::get('pembayaran', 'index')->name('pembayaran.index');
        Route::post('pembayaran/store', 'store')->name('pembayaran.store');
        Route::get('pembayaran/{id}', 'show')->name('pembayaran.show');
        Route::get('tagihan/{tagihanId}/details', 'getTagihanDetails')->name('tagihan.details');
        Route::delete('pembayaran/{id}/cancel', 'cancelPayment')->name('pembayaran.cancel');
    });
    Route::resource('pembayaran', WaliMuridPembayaranController::class)->except(['index', 'store', 'show']);
    
    // Kwitansi
    Route::get('kwitansi/{id}', [KwitansiPembayaranController::class, 'show'])->name('kwitansi.show');

    // Route untuk profile wali murid
    Route::get('profile', [WaliMuridProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [WaliMuridProfileController::class, 'update'])->name('profile.update');
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