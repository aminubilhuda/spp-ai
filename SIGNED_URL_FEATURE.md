# Fitur Signed URL Login untuk SPP-AI

## Deskripsi
Fitur ini memungkinkan user untuk login otomatis menggunakan signed URL yang aman dan temporary. Sangat berguna untuk memberikan akses langsung ke halaman tertentu tanpa perlu login manual.

## Komponen yang Ditambahkan

### 1. LoginController - Method loginUrl
**File**: `app/Http/Controllers/Auth/LoginController.php`

**Fungsi**: Menangani login otomatis via signed URL dengan validasi keamanan lengkap.

**Fitur Keamanan**:
- Validasi signature URL
- Validasi parameter yang diperlukan
- Validasi user exists
- Validasi URL redirect (hanya URL internal)
- Logging aktivitas login
- Error handling yang komprehensif

### 2. Helper Functions
**File**: `app/Helpers/Helpers.php`

#### `createSignedLoginUrl($userId, $redirectUrl, $expiresInDays = 7)`
- Membuat signed URL untuk login otomatis
- Validasi parameter dan user
- Default expiry 7 hari

#### `createPembayaranSignedUrl($pembayaranId, $waliId, $expiresInDays = 3)`
- Membuat signed URL khusus untuk akses pembayaran wali
- Validasi pembayaran dan wali
- Default expiry 3 hari (lebih pendek untuk keamanan)

### 3. WhatsApp Integration
**File**: `app/Http/Controllers/WhatsappController.php`

#### `sendPembayaranSignedUrl($pembayaranId, $waliId)`
- Mengirim signed URL via WhatsApp
- Validasi pembayaran dan wali
- Format pesan yang informatif

#### `formatPembayaranSignedUrlMessage($pembayaran, $signedUrl)`
- Format pesan WhatsApp dengan signed URL
- Informasi pembayaran lengkap
- Peringatan keamanan

### 4. Routes
**File**: `routes/web.php`

```php
// Route untuk login dengan signed URL
Route::get('login/login-url', [LoginController::class, 'loginUrl'])->name('login.url');

// Route untuk mengirim signed URL via WhatsApp (operator only)
Route::post('whatsapp/send-signed-url/{pembayaranId}/{waliId}', [WhatsappController::class, 'sendPembayaranSignedUrl'])->name('whatsapp.send-signed-url');

// Route testing (hanya development)
if (app()->environment('local')) {
    Route::get('test-signed-url', function () {
        // Generate test signed URL
    })->name('test.signed.url');
}
```

## Cara Kerja

### 1. Pembuatan Signed URL
```php
// Contoh penggunaan helper function
$signedUrl = createSignedLoginUrl(
    userId: 1,
    redirectUrl: route('operator.beranda'),
    expiresInDays: 7
);

// Untuk pembayaran wali
$signedUrl = createPembayaranSignedUrl(
    pembayaranId: 1,
    waliId: 2,
    expiresInDays: 3
);
```

### 2. Validasi dan Login
```php
// Di LoginController::loginUrl()
if (!$request->hasValidSignature()) {
    abort(403, 'URL tidak valid atau sudah kadaluarsa');
}

$user = \App\Models\User::find($request->user_id);
Auth::login($user);

return redirect()->to($request->url);
```

### 3. Pengiriman via WhatsApp
```php
// Di WhatsappController
$result = $this->sendPembayaranSignedUrl($pembayaranId, $waliId);
```

## Fitur Keamanan

### 1. URL Signature
- Laravel's built-in signed URL mechanism
- Hash-based signature untuk mencegah tampering
- Timestamp untuk expiry control

### 2. Parameter Validation
- Validasi user exists sebelum login
- Validasi URL redirect (hanya internal URLs)
- Validasi parameter yang diperlukan

### 3. Access Control
- Validasi wali memiliki akses ke pembayaran
- Role-based access control
- Logging semua aktivitas login

### 4. Expiry Management
- Default 7 hari untuk login umum
- Default 3 hari untuk pembayaran (lebih pendek)
- Configurable expiry time

## Contoh Penggunaan

### 1. Operator Mengirim Signed URL ke Wali
```php
// Di controller operator
public function sendPembayaranDetail($pembayaranId)
{
    $pembayaran = Pembayaran::findOrFail($pembayaranId);
    $waliId = $pembayaran->wali_id;
    
    $result = app(WhatsappController::class)
        ->sendPembayaranSignedUrl($pembayaranId, $waliId);
    
    return response()->json($result);
}
```

### 2. Wali Mengakses via Signed URL
1. Wali menerima pesan WhatsApp dengan signed URL
2. Klik link di WhatsApp
3. Otomatis login dan redirect ke detail pembayaran
4. Tidak perlu login manual

### 3. Testing Signed URL
```bash
# Akses route testing (hanya development)
GET /test-signed-url

# Response:
{
    "signed_url": "http://localhost/login/login-url?signature=...",
    "expires_at": "2024-01-25 10:30:00",
    "note": "URL ini hanya untuk testing, hapus di production"
}
```

## Error Handling

### 1. Invalid Signature
```php
abort(403, 'URL tidak valid atau sudah kadaluarsa');
```

### 2. Missing Parameters
```php
abort(400, 'Parameter tidak lengkap');
```

### 3. User Not Found
```php
abort(404, 'User tidak ditemukan');
```

### 4. Invalid Redirect URL
```php
abort(400, 'URL redirect tidak valid');
```

## Logging

### 1. Successful Login
```php
\Log::info('User login via signed URL', [
    'user_id' => $userId,
    'user_name' => $user->name,
    'redirect_url' => $redirectUrl,
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent()
]);
```

### 2. Error Logging
```php
\Log::error('Error in loginUrl', [
    'error' => $e->getMessage(),
    'request_data' => $request->all(),
    'ip_address' => $request->ip()
]);
```

## Best Practices

### 1. Security
- Gunakan expiry time yang pendek untuk data sensitif
- Validasi semua parameter input
- Log semua aktivitas login
- Jangan share signed URL di tempat publik

### 2. User Experience
- Berikan informasi jelas tentang expiry time
- Format pesan WhatsApp yang mudah dibaca
- Redirect ke halaman yang relevan

### 3. Performance
- Gunakan eager loading untuk relasi
- Cache signed URL jika diperlukan
- Optimize database queries

## Troubleshooting

### 1. URL Expired
- Cek timestamp di signed URL
- Generate ulang signed URL
- Pastikan server timezone benar

### 2. Invalid Signature
- Cek APP_KEY di .env
- Pastikan URL tidak dimodifikasi
- Cek Laravel version compatibility

### 3. User Not Found
- Validasi user_id di database
- Cek soft deletes jika ada
- Pastikan user masih aktif

### 4. WhatsApp Not Sent
- Cek konfigurasi Fonnte API
- Validasi nomor WhatsApp
- Cek log error di storage/logs

## Future Enhancements

1. **Rate Limiting**: Batasi jumlah signed URL per user
2. **Audit Trail**: Track semua akses signed URL
3. **Revoke URL**: Kemampuan untuk membatalkan signed URL
4. **Custom Expiry**: Expiry time yang dapat dikonfigurasi per use case
5. **Analytics**: Track usage dan effectiveness
6. **Mobile Deep Link**: Support untuk mobile app 