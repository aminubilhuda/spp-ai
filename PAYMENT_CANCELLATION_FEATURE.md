# Fitur Pembatalan Pembayaran untuk Wali Murid

## Deskripsi
Fitur ini memungkinkan wali murid untuk membatalkan pembayaran yang belum dikonfirmasi oleh operator. Pembatalan hanya dapat dilakukan dalam batas waktu tertentu dan dengan validasi keamanan yang ketat.

## Komponen yang Ditambahkan

### 1. Controller Methods
**File**: `app/Http/Controllers/WaliMuridPembayaranController.php`

#### `destroy($id)` - Method untuk pembatalan via form
- Validasi akses wali ke pembayaran
- Validasi status pembayaran (belum dikonfirmasi)
- Validasi kepemilikan pembayaran
- Validasi waktu pembayaran (maksimal 24 jam)
- Hapus file bukti pembayaran
- Log aktivitas pembatalan

#### `cancelPayment($id)` - Method untuk pembatalan via AJAX
- Sama dengan `destroy()` tetapi return JSON response
- Untuk interaksi yang lebih smooth di frontend

### 2. Helper Function
**File**: `app/Helpers/Helpers.php`

#### `canCancelPayment($pembayaran, $waliId)`
- Cek apakah pembayaran bisa dibatalkan
- Return array dengan informasi lengkap
- Termasuk alasan mengapa tidak bisa dibatalkan

### 3. Routes
**File**: `routes/web.php`

```php
Route::delete('pembayaran/{id}/cancel', 'cancelPayment')->name('pembayaran.cancel');
```

### 4. Views
**Files**: 
- `resources/views/wali/pembayaran_index.blade.php`
- `resources/views/wali/pembayaran_show.blade.php`

- Tombol batalkan dengan kondisi
- Modal konfirmasi pembatalan
- JavaScript untuk handle AJAX request

## Aturan Pembatalan

### 1. Kondisi yang Diizinkan
- Pembayaran belum dikonfirmasi oleh operator
- Pembayaran dibuat oleh wali yang sedang login
- Pembayaran dibuat dalam 24 jam terakhir

### 2. Kondisi yang Tidak Diizinkan
- Pembayaran sudah dikonfirmasi
- Pembayaran dibuat oleh wali lain
- Pembayaran dibuat lebih dari 24 jam yang lalu
- Wali tidak memiliki akses ke siswa tersebut

### 3. Proses Pembatalan
1. Validasi semua kondisi di atas
2. Hapus file bukti pembayaran dari storage
3. Hapus record pembayaran dari database
4. Log aktivitas pembatalan
5. Return response ke user

## Fitur Keamanan

### 1. Validasi Akses
```php
// Cek wali memiliki akses ke siswa
$siswaIds = Auth::user()->siswa->pluck('id');
$pembayaran = Pembayaran::whereHas('tagihan', function($q) use ($siswaIds) {
    $q->whereIn('siswa_id', $siswaIds);
})->find($id);
```

### 2. Validasi Kepemilikan
```php
// Cek pembayaran dibuat oleh wali yang sedang login
if ($pembayaran->wali_id !== Auth::id()) {
    return response()->json([
        'success' => false,
        'message' => 'Anda hanya dapat membatalkan pembayaran yang Anda buat'
    ], 403);
}
```

### 3. Validasi Waktu
```php
// Cek waktu pembayaran (maksimal 24 jam)
$hoursDiff = $currentTime->diffInHours($createdTime);
if ($hoursDiff > 24) {
    return response()->json([
        'success' => false,
        'message' => 'Pembayaran hanya dapat dibatalkan dalam waktu 24 jam setelah dibuat'
    ], 400);
}
```

### 4. Database Transaction
```php
DB::beginTransaction();
try {
    // Hapus file bukti pembayaran
    if ($pembayaran->bukti_bayar) {
        Storage::disk('public')->delete($pembayaran->bukti_bayar);
    }
    
    // Hapus pembayaran
    $pembayaran->delete();
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    throw $e;
}
```

## UI/UX Features

### 1. Tombol Batalkan
- Hanya muncul jika pembayaran bisa dibatalkan
- Icon dan warna yang jelas (merah)
- Tooltip dengan informasi

### 2. Modal Konfirmasi
- Peringatan yang jelas
- Informasi pembayaran yang akan dibatalkan
- Konfirmasi dua langkah

### 3. Feedback User
- Loading state saat proses
- Success/error messages
- Auto refresh setelah berhasil

## Logging & Audit Trail

### 1. Successful Cancellation
```php
\Log::info('Pembayaran dibatalkan oleh wali', [
    'pembayaran_id' => $id,
    'wali_id' => Auth::id(),
    'wali_name' => Auth::user()->name,
    'siswa_name' => $pembayaran->tagihan->siswa->nama,
    'jumlah_dibayar' => $pembayaran->jumlah_dibayar,
    'created_at' => $createdTime,
    'cancelled_at' => $currentTime,
    'hours_diff' => $hoursDiff
]);
```

### 2. Failed Attempts
```php
\Log::warning('Unauthorized cancellation attempt', [
    'pembayaran_id' => $id,
    'wali_id' => Auth::id(),
    'reason' => 'Pembayaran sudah dikonfirmasi'
]);
```

## Error Handling

### 1. HTTP Status Codes
- `200` - Pembatalan berhasil
- `400` - Bad Request (validasi gagal)
- `403` - Forbidden (tidak punya akses)
- `404` - Pembayaran tidak ditemukan
- `500` - Server error

### 2. Error Messages
- Pesan error yang jelas dan informatif
- Tidak mengekspos informasi sensitif
- Memberikan panduan untuk user

## Contoh Penggunaan

### 1. Cek Apakah Bisa Dibatalkan
```php
$cancelInfo = canCancelPayment($pembayaran, auth()->id());

if ($cancelInfo['can_cancel']) {
    // Tampilkan tombol batalkan
    echo "Bisa dibatalkan, sisa waktu: {$cancelInfo['remaining_hours']} jam";
} else {
    // Tampilkan alasan tidak bisa dibatalkan
    foreach ($cancelInfo['reasons'] as $reason) {
        echo "- $reason";
    }
}
```

### 2. AJAX Request
```javascript
$.ajax({
    url: `/walimurid/pembayaran/${pembayaranId}/cancel`,
    type: 'DELETE',
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    success: function(response) {
        if (response.success) {
            // Handle success
            location.reload();
        }
    },
    error: function(xhr) {
        // Handle error
        console.log(xhr.responseJSON.message);
    }
});
```

## Testing

### 1. Test Cases
- ✅ Pembayaran belum dikonfirmasi - bisa dibatalkan
- ✅ Pembayaran sudah dikonfirmasi - tidak bisa dibatalkan
- ✅ Pembayaran > 24 jam - tidak bisa dibatalkan
- ✅ Pembayaran wali lain - tidak bisa dibatalkan
- ✅ File bukti terhapus setelah pembatalan
- ✅ Log aktivitas tercatat

### 2. Edge Cases
- Pembayaran dengan file bukti yang tidak ada
- Pembayaran yang sudah dihapus
- Multiple request bersamaan
- Network error saat proses

## Best Practices

### 1. Security
- Validasi semua input dan akses
- Gunakan database transaction
- Log semua aktivitas penting
- Jangan expose informasi sensitif

### 2. User Experience
- Berikan feedback yang jelas
- Konfirmasi sebelum menghapus
- Loading state saat proses
- Error handling yang graceful

### 3. Performance
- Gunakan eager loading untuk relasi
- Optimize database queries
- Handle file deletion dengan baik
- Rate limiting jika diperlukan

## Future Enhancements

1. **Soft Delete**: Simpan history pembatalan
2. **Email Notification**: Kirim email ke operator
3. **Reason for Cancellation**: Wali bisa berikan alasan
4. **Partial Cancellation**: Batalkan sebagian pembayaran
5. **Admin Override**: Admin bisa batalkan pembayaran yang sudah dikonfirmasi
6. **Audit Report**: Laporan pembatalan pembayaran 