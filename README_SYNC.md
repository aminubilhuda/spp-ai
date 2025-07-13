# Sistem Sinkronisasi Database Hybrid Offline-Online

## Overview

Sistem sinkronisasi database hybrid memungkinkan aplikasi SPP berjalan secara offline di komputer bendahara dan sinkronisasi data dengan server online secara otomatis atau manual. Sistem ini mendukung sinkronisasi dua arah untuk semua tabel utama.

## Fitur Utama

### 1. Sinkronisasi Semua Tabel
- **Master Data**: Users, Settings, Tahun Pelajaran, Jurusan, Biaya, Bank Sekolah, Bank, Instansi Setting
- **Transaction Data**: Pembayaran, Tagihan, Tagihan Detail, Siswa, Pengeluaran Kas

### 2. Sinkronisasi Dua Arah
- **Local → Online**: Data dari komputer bendahara dikirim ke server online
- **Online → Local**: Data dari server online diterima oleh komputer bendahara

### 3. Status Tracking
- **Pending**: Data belum disinkronisasi
- **Synced**: Data berhasil disinkronisasi
- **Failed**: Data gagal disinkronisasi (bisa di-retry)

### 4. Monitoring Real-time
- Dashboard monitoring status sinkronisasi
- Log sinkronisasi real-time
- Cek koneksi internet otomatis
- Statistik per tabel

## Konfigurasi

### 1. Environment Variables
Tambahkan konfigurasi berikut di file `.env`:

```env
# Database Sync Configuration
ONLINE_API_URL=https://your-domain.com
SYNC_API_KEY=your-secret-api-key
SYNC_INTERVAL=300
```

### 2. Konfigurasi di `config/app.php`
```php
'online_api_url' => env('ONLINE_API_URL', 'https://your-domain.com'),
'sync_api_key' => env('SYNC_API_KEY', 'your-secret-api-key'),
'sync_interval' => env('SYNC_INTERVAL', 300), // 5 minutes in seconds
```

## Struktur Database

### Kolom Sinkronisasi
Setiap tabel memiliki kolom sinkronisasi:
- `sync_id`: ID unik untuk tracking sinkronisasi
- `synced_at`: Timestamp kapan terakhir disinkronisasi
- `sync_status`: Status sinkronisasi (pending/synced/failed)
- `source_system`: Sumber data (local/online)

### Migration
```bash
php artisan migrate
```

## Komponen Sistem

### 1. Service: `DatabaseSyncService`
**File**: `app/Services/DatabaseSyncService.php`

Fitur:
- Sinkronisasi ke online (`syncToOnline()`)
- Sinkronisasi dari online (`syncFromOnline()`)
- Cek koneksi internet dengan multiple endpoint
- Reset status failed untuk retry
- Generate sync ID unik

### 2. API Controller: `SyncController`
**File**: `app/Http/Controllers/Api/SyncController.php`

Endpoint:
- `GET /api/sync/data` - Ambil data untuk sinkronisasi
- `POST /api/sync/user` - Sync User
- `POST /api/sync/setting` - Sync Setting
- `POST /api/sync/tahun-pelajaran` - Sync Tahun Pelajaran
- `POST /api/sync/jurusan` - Sync Jurusan
- `POST /api/sync/biaya` - Sync Biaya
- `POST /api/sync/bank-sekolah` - Sync Bank Sekolah
- `POST /api/sync/bank` - Sync Bank
- `POST /api/sync/instansi-setting` - Sync Instansi Setting
- `POST /api/sync/pembayaran` - Sync Pembayaran
- `POST /api/sync/tagihan` - Sync Tagihan
- `POST /api/sync/tagihan-detail` - Sync Tagihan Detail
- `POST /api/sync/siswa` - Sync Siswa
- `POST /api/sync/pengeluaran-kas` - Sync Pengeluaran Kas

### 3. Web Controller: `SyncStatusController`
**File**: `app/Http/Controllers/SyncStatusController.php`

Fitur:
- Dashboard monitoring sinkronisasi
- Cek koneksi internet
- Manual sync per tabel
- Reset failed sync
- Statistik dan log sinkronisasi

### 4. Command: `SyncDatabaseCommand`
**File**: `app/Console/Commands/SyncDatabaseCommand.php`

Command:
```bash
# Sinkronisasi dua arah
php artisan sync:database both

# Sinkronisasi ke online saja
php artisan sync:database to-online

# Sinkronisasi dari online saja
php artisan sync:database from-online
```

### 5. Scheduled Task
**File**: `app/Console/Kernel.php`

Auto sync setiap 5 menit:
```php
$schedule->command('sync:database both')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();
```

## Halaman Monitoring

### URL: `/operator/sync/status`

Fitur:
- **Status Koneksi**: Real-time status internet connection
- **Master Data Stats**: Statistik tabel master data dengan progress bar
- **Transaction Data Stats**: Statistik tabel transaksi dengan progress bar
- **Detail Table**: Tabel detail status per tabel
- **Sync Log**: Log sinkronisasi real-time
- **Manual Actions**: 
  - Sync Manual (semua tabel)
  - Sync per tabel
  - Reset failed sync
  - Cek koneksi

## Cara Penggunaan

### 1. Setup Awal
```bash
# Jalankan migration
php artisan migrate

# Generate API key (opsional)
php artisan make:api-key

# Test sinkronisasi
php artisan sync:database both
```

### 2. Monitoring via Web
1. Buka `/operator/sync/status`
2. Monitor status koneksi dan sinkronisasi
3. Gunakan tombol manual sync jika diperlukan
4. Reset failed sync jika ada error

### 3. Monitoring via Command
```bash
# Cek status sinkronisasi
php artisan sync:database both

# Sync manual ke online
php artisan sync:database to-online

# Sync manual dari online
php artisan sync:database from-online
```

### 4. Auto Sync
Sistem akan otomatis sinkronisasi setiap 5 menit jika ada data pending.

## Troubleshooting

### 1. Koneksi Internet
- Sistem menggunakan multiple endpoint untuk cek koneksi
- Endpoint: ipify.org, httpbin.org, google.com
- Timeout 10 detik per endpoint

### 2. Sync Failed
- Cek log di `storage/logs/laravel.log`
- Reset failed sync via web interface
- Cek konfigurasi API URL dan key

### 3. Data Conflict
- Sistem menggunakan `sync_id` untuk tracking
- Data dengan `sync_id` sama akan di-update
- Data baru akan di-insert

### 4. Performance
- Sinkronisasi dilakukan per tabel
- Batch processing untuk data besar
- Timeout handling untuk koneksi lambat

## Security

### 1. API Authentication
- Menggunakan Bearer token
- Validasi API key di setiap request
- Rate limiting (bisa ditambahkan)

### 2. Data Validation
- Validasi data sebelum sync
- Sanitasi input
- Error handling yang aman

### 3. Logging
- Log semua aktivitas sinkronisasi
- Error tracking
- Audit trail

## Model Updates

Semua model utama sudah diupdate dengan kolom sinkronisasi:

```php
protected $fillable = [
    // ... existing fields
    'sync_id', 'synced_at', 'sync_status', 'source_system',
];
```

## Routes

### Web Routes
```php
Route::prefix('operator')->middleware(['auth', 'auth.operator'])->group(function () {
    Route::get('sync/status', [SyncStatusController::class, 'index'])->name('sync.status');
    Route::get('sync/status/data', [SyncStatusController::class, 'getStatus'])->name('sync.status.data');
    Route::get('sync/check-connection', [SyncStatusController::class, 'checkConnection'])->name('sync.check-connection');
    Route::post('sync/manual', [SyncStatusController::class, 'manualSync'])->name('sync.manual');
    Route::post('sync/table/{tableName}', [SyncStatusController::class, 'syncTable'])->name('sync.table');
    Route::post('sync/reset-failed', [SyncStatusController::class, 'resetFailed'])->name('sync.reset-failed');
});
```

### API Routes
```php
Route::prefix('sync')->group(function () {
    Route::get('/data', [SyncController::class, 'getSyncData']);
    // ... semua endpoint sync
});
```

## Testing

### 1. Unit Test
```bash
php artisan test --filter=SyncTest
```

### 2. Manual Test
```bash
# Test koneksi
curl -X GET "http://localhost/api/sync/data" \
  -H "Authorization: Bearer your-api-key"

# Test sync
curl -X POST "http://localhost/api/sync/user" \
  -H "Authorization: Bearer your-api-key" \
  -H "Content-Type: application/json" \
  -d '{"data": {...}, "sync_id": "test_123"}'
```

## Deployment

### 1. Production Setup
1. Set environment variables
2. Jalankan migration
3. Setup cron job untuk auto sync
4. Monitor log dan performance

### 2. Cron Job
```bash
# Tambahkan ke crontab
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## Monitoring & Maintenance

### 1. Log Monitoring
- Monitor `storage/logs/laravel.log`
- Cek error sinkronisasi
- Track performance metrics

### 2. Database Maintenance
- Monitor ukuran tabel
- Optimize query jika diperlukan
- Backup data secara berkala

### 3. Performance Optimization
- Index pada kolom sinkronisasi
- Batch processing untuk data besar
- Connection pooling

## Support

Untuk masalah atau pertanyaan terkait sistem sinkronisasi:
1. Cek log error di `storage/logs/laravel.log`
2. Monitor dashboard sinkronisasi
3. Test koneksi internet
4. Reset failed sync jika diperlukan 