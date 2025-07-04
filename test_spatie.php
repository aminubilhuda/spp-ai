<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TagihanDetail;
use App\Models\Siswa;
use App\Models\Wali;

echo "Testing Spatie Model Status...\n";

try {
    // Cek apakah ada data yang diperlukan
    $siswa = Siswa::first();
    $tagihan = Tagihan::first();
    $tagihanDetail = TagihanDetail::first();
    $wali = Wali::first();
    
    if (!$siswa || !$tagihan || !$tagihanDetail || !$wali) {
        echo "Data tidak lengkap untuk testing\n";
        echo "Siswa: " . ($siswa ? 'Ada' : 'Tidak ada') . "\n";
        echo "Tagihan: " . ($tagihan ? 'Ada' : 'Tidak ada') . "\n";
        echo "TagihanDetail: " . ($tagihanDetail ? 'Ada' : 'Tidak ada') . "\n";
        echo "Wali: " . ($wali ? 'Ada' : 'Tidak ada') . "\n";
        exit;
    }
    
    // Buat pembayaran test jika tidak ada
    $pembayaran = Pembayaran::first();
    if (!$pembayaran) {
        echo "Creating test pembayaran...\n";
        $pembayaran = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'tagihan_detail_id' => $tagihanDetail->id,
            'wali_id' => $wali->id,
            'status_konfirmasi' => 'Belum Dikonfirmasi',
            'jumlah_dibayar' => 100000,
            'metode_pembayaran' => 'Cash',
            'tanggal_bayar' => now(),
            'user_id' => 1
        ]);
        echo "Pembayaran created with ID: " . $pembayaran->id . "\n";
    }
    
    echo "Found pembayaran ID: " . $pembayaran->id . "\n";
    
    // Test setStatus
    $pembayaran->setStatus('pending', 'Testing dari script');
    echo "Status set successfully!\n";
    
    // Test get status
    $status = $pembayaran->status;
    echo "Current status: " . $status . "\n";
    
    // Test get status object
    $statusObj = $pembayaran->status();
    if ($statusObj) {
        echo "Status object - Name: " . $statusObj->name . ", Reason: " . $statusObj->reason . "\n";
    }
    
    // Test get all statuses
    $allStatuses = $pembayaran->statuses;
    echo "Total statuses: " . $allStatuses->count() . "\n";
    
    // Test set another status
    $pembayaran->setStatus('confirmed', 'Dikonfirmasi oleh admin');
    echo "Second status set successfully!\n";
    echo "New status: " . $pembayaran->status . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "Test completed!\n"; 