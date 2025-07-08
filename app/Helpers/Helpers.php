<?php

function bulanSPP() {
    return [
        7,8,9,10,11,12,1,2,3,4,5,6
    ];
}

function formatRupiah($nominal, $prefix = null) {
    $prefix = $prefix ? $prefix : 'Rp. ';
    return $prefix . number_format($nominal, 0, ',', '.');
}

function formatTanggalIndonesia($tanggal, $format = 'd F Y') {
    if (!$tanggal) {
        return '-';
    }
    return \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat($format);
}

function formatTanggalWaktuIndonesia($tanggal, $format = 'd F Y, H:i') {
    if (!$tanggal) {
        return '-';
    }
    return \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat($format);
}

/**
 * Mendapatkan pengaturan instansi
 * 
 * @param string|null $key
 * @return mixed
 */
function getInstansiSetting($key = null) {
    $setting = \App\Models\Setting::getInstansiSettings();
    
    if ($key) {
        return $setting->$key ?? '';
    }
    
    return $setting;
}

/**
 * Mendapatkan URL logo instansi yang aman
 * 
 * @return string
 */
function getInstansiLogoUrl() {
    $logoPath = getInstansiSetting('logo_instansi');
    
    if (!$logoPath) {
        return '';
    }
    
    // Bersihkan path dari 'storage/' jika ada
    $cleanPath = str_replace('storage/', '', $logoPath);
    $fullPath = storage_path('app/public/' . $cleanPath);
    
    // Cek apakah file ada
    if (!file_exists($fullPath)) {
        return '';
    }
    
    // Cek apakah file adalah gambar
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedTypes)) {
        return '';
    }
    
    // Baca file dan encode ke base64
    try {
        $imageData = file_get_contents($fullPath);
        $mimeType = mime_content_type($fullPath);
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    } catch (\Exception $e) {
        return '';
    }
}

/**
 * Mendapatkan URL logo instansi untuk storage URL (tidak base64)
 * 
 * @return string
 */
function getInstansiLogoStorageUrl() {
    $logoPath = getInstansiSetting('logo_instansi');
    
    if (!$logoPath) {
        return '';
    }
    
    return Storage::disk('public')->url($logoPath);
}

/**
 * Mendapatkan URL TTD penanggung jawab yang aman
 * 
 * @return string
 */
function getInstansiTtdUrl() {
    $ttdPath = getInstansiSetting('ttd_penanggung_jawab');
    
    if (!$ttdPath) {
        return '';
    }
    
    // Bersihkan path dari 'storage/' jika ada
    $cleanPath = str_replace('storage/', '', $ttdPath);
    $fullPath = storage_path('app/public/' . $cleanPath);
    
    // Cek apakah file ada
    if (!file_exists($fullPath)) {
        return '';
    }
    
    // Cek apakah file adalah gambar
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedTypes)) {
        return '';
    }
    
    // Baca file dan encode ke base64
    try {
        $imageData = file_get_contents($fullPath);
        $mimeType = mime_content_type($fullPath);
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    } catch (\Exception $e) {
        return '';
    }
}

/**
 * Mendapatkan URL TTD penanggung jawab untuk storage URL (tidak base64)
 * 
 * @return string
 */
function getInstansiTtdStorageUrl() {
    $ttdPath = getInstansiSetting('ttd_penanggung_jawab');
    
    if (!$ttdPath) {
        return '';
    }
    
    return Storage::disk('public')->url($ttdPath);
}

/**
 * Check if payment can be cancelled
 * 
 * @param \App\Models\Pembayaran $pembayaran
 * @param int $userId
 * @return array
 */
function canCancelPayment($pembayaran, $userId) {
    // Default response
    $response = [
        'can_cancel' => false,
        'message' => 'Pembayaran tidak dapat dibatalkan',
        'reason' => ''
    ];
    
    // Check if pembayaran exists
    if (!$pembayaran) {
        $response['reason'] = 'Data pembayaran tidak ditemukan';
        return $response;
    }
    
    // Check if tagihan and siswa exist
    if (!$pembayaran->tagihan || !$pembayaran->tagihan->siswa) {
        $response['reason'] = 'Data tagihan atau siswa tidak ditemukan';
        return $response;
    }
    
    // Check if user is authorized (pembayaran belongs to user's student)
    if ($pembayaran->tagihan->siswa->wali_id != $userId) {
        $response['reason'] = 'Anda tidak memiliki akses untuk membatalkan pembayaran ini';
        return $response;
    }
    
    // Check if payment is already confirmed
    if ($pembayaran->status_konfirmasi == 'Sudah Dikonfirmasi') {
        $response['reason'] = 'Pembayaran sudah dikonfirmasi dan tidak dapat dibatalkan';
        return $response;
    }
    
    // Check if payment is already cancelled
    if ($pembayaran->status == 'Dibatalkan') {
        $response['reason'] = 'Pembayaran sudah dibatalkan';
        return $response;
    }
    
    // Check if payment is within cancellation time limit (24 hours)
    $paymentTime = \Carbon\Carbon::parse($pembayaran->created_at);
    $now = \Carbon\Carbon::now();
    $hoursDiff = $paymentTime->diffInHours($now);
    
    if ($hoursDiff > 24) {
        $response['reason'] = 'Pembayaran sudah melewati batas waktu pembatalan (24 jam)';
        return $response;
    }
    
    // If all checks pass, payment can be cancelled
    $response['can_cancel'] = true;
    $response['message'] = 'Pembayaran dapat dibatalkan';
    $response['reason'] = '';
    
    return $response;
}

function terbilang($angka) {
    $angka = abs($angka);
    $baca = array(
        '', 'satu', 'dua', 'tiga', 'empat', 'lima',
        'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
    );
    $terbilang = '';

    if ($angka < 12) {
        $terbilang = ' ' . $baca[$angka];
    } elseif ($angka < 20) {
        $terbilang = terbilang($angka - 10) . ' belas';
    } elseif ($angka < 100) {
        $terbilang = terbilang((int)($angka / 10)) . ' puluh' . terbilang($angka % 10);
    } elseif ($angka < 200) {
        $terbilang = ' seratus' . terbilang($angka - 100);
    } elseif ($angka < 1000) {
        $terbilang = terbilang((int)($angka / 100)) . ' ratus' . terbilang($angka % 100);
    } elseif ($angka < 2000) {
        $terbilang = ' seribu' . terbilang($angka - 1000);
    } elseif ($angka < 1000000) {
        $terbilang = terbilang((int)($angka / 1000)) . ' ribu' . terbilang($angka % 1000);
    } elseif ($angka < 1000000000) {
        $terbilang = terbilang((int)($angka / 1000000)) . ' juta' . terbilang($angka % 1000000);
    } elseif ($angka < 1000000000000) {
        $terbilang = terbilang((int)($angka / 1000000000)) . ' milyar' . terbilang($angka % 1000000000);
    } elseif ($angka < 1000000000000000) {
        $terbilang = terbilang((int)($angka / 1000000000000)) . ' trilyun' . terbilang($angka % 1000000000000);
    }

    return $terbilang;
}