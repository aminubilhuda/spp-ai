# Dokumentasi Penggunaan Spatie Model Status

## Overview
Package [Spatie Laravel Model Status](https://github.com/spatie/laravel-model-status) telah berhasil diimplementasikan di project SPP-AI untuk mengelola status pembayaran dan tagihan dengan lebih robust.

## Struktur Database

### Tabel `statuses`
```sql
CREATE TABLE statuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    reason TEXT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_model (model_type, model_id),
    INDEX idx_model_status (model_type, model_id, name)
);
```

## Model yang Menggunakan Status

### 1. Model Pembayaran
```php
use Spatie\ModelStatus\HasStatuses;

class Pembayaran extends Model
{
    use HasStatuses;
    
    public function isValidStatus(string $name, ?string $reason = null): bool
    {
        $validStatuses = [
            'pending',      // Menunggu konfirmasi
            'confirmed',    // Sudah dikonfirmasi
            'rejected',     // Ditolak
            'cancelled'     // Dibatalkan
        ];
        return in_array($name, $validStatuses);
    }
}
```

### 2. Model TagihanDetail
```php
use Spatie\ModelStatus\HasStatuses;

class TagihanDetail extends Model
{
    use HasStatuses;
    
    public function isValidStatus(string $name, ?string $reason = null): bool
    {
        $validStatuses = [
            'unpaid',       // Belum dibayar
            'partial',      // Dibayar sebagian
            'paid',         // Lunas
            'overdue'       // Jatuh tempo
        ];
        return in_array($name, $validStatuses);
    }
}
```

## Cara Penggunaan

### 1. Set Status
```php
// Set status dengan alasan
$pembayaran->setStatus('pending', 'Menunggu konfirmasi dari operator');
$pembayaran->setStatus('confirmed', 'Pembayaran dikonfirmasi oleh admin');

// Set status tanpa alasan
$pembayaran->setStatus('rejected');
```

### 2. Get Current Status
```php
// Get status name (string)
$statusName = $pembayaran->status; // 'pending'

// Get status object
$statusObject = $pembayaran->status(); // Status model instance
echo $statusObject->name; // 'pending'
echo $statusObject->reason; // 'Menunggu konfirmasi dari operator'
```

### 3. Get Status History
```php
// Get all statuses
$allStatuses = $pembayaran->statuses; // Collection of Status models

// Get latest status of specific type
$latestPending = $pembayaran->latestStatus('pending');

// Get latest status from multiple types
$latestStatus = $pembayaran->latestStatus(['pending', 'confirmed']);
```

### 4. Check Status
```php
// Check if has specific status
$hasStatus = $pembayaran->hasStatus('pending'); // true/false

// Check if has ever had status
$hasEverHad = $pembayaran->hasEverHadStatus('rejected');

// Check if has never had status
$hasNeverHad = $pembayaran->hasNeverHadStatus('cancelled');
```

### 5. Query Models by Status
```php
// Get all models with specific status
$pendingPayments = Pembayaran::currentStatus('pending');

// Get all models without specific status
$nonPendingPayments = Pembayaran::otherCurrentStatus('pending');

// Get models with multiple statuses
$activePayments = Pembayaran::currentStatus(['pending', 'confirmed']);
```

### 6. Delete Status
```php
// Delete specific status
$pembayaran->deleteStatus('pending');

// Delete multiple statuses
$pembayaran->deleteStatus(['pending', 'rejected']);
```

## Implementasi di Controller

### PembayaranController
```php
public function store(Request $request)
{
    // ... create pembayaran ...
    
    // Set status awal
    $statusReason = $isWali ? 'Pembayaran dibuat oleh wali, menunggu konfirmasi' : 'Pembayaran dibuat oleh operator';
    $pembayaran->setStatus('pending', $statusReason);
}

public function confirm($id)
{
    $pembayaran = Pembayaran::findOrFail($id);
    
    // Update status lama (backward compatibility)
    $pembayaran->status_konfirmasi = 'Sudah Dikonfirmasi';
    $pembayaran->save();
    
    // Set status baru
    $pembayaran->setStatus('confirmed', 'Pembayaran dikonfirmasi oleh operator: ' . auth()->user()->name);
}
```

## Events

Package ini mengirim event `StatusUpdated` saat status berubah:

```php
use Spatie\ModelStatus\Events\StatusUpdated;

Event::listen(StatusUpdated::class, function ($event) {
    $oldStatus = $event->oldStatus; // Status sebelumnya (null jika pertama kali)
    $newStatus = $event->newStatus; // Status baru
    $model = $event->model; // Model yang statusnya berubah
    
    // Handle status change
    Log::info("Status changed from {$oldStatus?->name} to {$newStatus->name}");
});
```

## Migration dari Status Lama

Untuk migrasi dari status lama ke sistem baru:

```php
// Migrate existing data
$pembayarans = Pembayaran::all();
foreach ($pembayarans as $pembayaran) {
    if ($pembayaran->status_konfirmasi === 'Belum Dikonfirmasi') {
        $pembayaran->setStatus('pending', 'Migrated from old system');
    } elseif ($pembayaran->status_konfirmasi === 'Sudah Dikonfirmasi') {
        $pembayaran->setStatus('confirmed', 'Migrated from old system');
    }
}
```

## Keuntungan Implementasi

1. **Audit Trail**: Setiap perubahan status tercatat dengan timestamp dan alasan
2. **Flexibility**: Mudah menambah status baru tanpa mengubah database schema
3. **Validation**: Built-in validation untuk status transitions
4. **Query Power**: Kemampuan query yang powerful berdasarkan status
5. **Event System**: Otomatis trigger event saat status berubah
6. **Backward Compatibility**: Tetap mendukung field status lama

## Testing

File `test_status.php` tersedia untuk testing package:

```bash
php test_status.php
```

## Konfigurasi

File konfigurasi: `config/model-status.php`

```php
return [
    'status_model' => App\Models\Status::class,
    'status_attribute' => 'status',
    'model_primary_key_attribute' => 'model_id',
];
``` 