<?php
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