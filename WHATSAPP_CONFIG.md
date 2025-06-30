# Konfigurasi WhatsApp Fonnte

Dokumentasi untuk mengatur notifikasi WhatsApp menggunakan Fonnte API dalam sistem SPP.

## Variabel Environment

Tambahkan variabel berikut ke file `.env`:

```env
# Fonnte WhatsApp Configuration
FONNTE_TOKEN=your_fonnte_token_here
FONNTE_ENABLED=true

# Notification Settings
FONNTE_NOTIF_PEMBAYARAN=true
FONNTE_NOTIF_REMINDER=true
FONNTE_NOTIF_KONFIRMASI=true
FONNTE_NOTIF_SISTEM=true

# WhatsApp Settings
FONNTE_COUNTRY_CODE=62
FONNTE_TYPING=false
FONNTE_DELAY=2
```

## Penjelasan Variabel

### Konfigurasi Dasar
- `FONNTE_TOKEN`: Token API dari Fonnte (wajib)
- `FONNTE_ENABLED`: Mengaktifkan/menonaktifkan fitur WhatsApp secara global

### Pengaturan Notifikasi
- `FONNTE_NOTIF_PEMBAYARAN`: Notifikasi ketika ada pembayaran baru (default: true)
- `FONNTE_NOTIF_REMINDER`: Reminder pembayaran yang belum lunas (default: true)
- `FONNTE_NOTIF_KONFIRMASI`: Konfirmasi pembayaran berhasil (default: true)
- `FONNTE_NOTIF_SISTEM`: Notifikasi sistem umum (default: true)

### Pengaturan WhatsApp
- `FONNTE_COUNTRY_CODE`: Kode negara (default: 62 untuk Indonesia)
- `FONNTE_TYPING`: Menampilkan indikator typing (default: false)
- `FONNTE_DELAY`: Delay antar pesan dalam detik (default: 2)

## Cara Penggunaan

### 1. Notifikasi Pembayaran
```php
use App\Services\WhatsappFonnteServices;

$whatsapp = new WhatsappFonnteServices();
$whatsapp->sendPembayaranNotification($pembayaran);
```

### 2. Reminder Pembayaran
```php
$whatsapp->sendReminderPembayaran($tagihan);
```

### 3. Konfirmasi Pembayaran
```php
$whatsapp->sendKonfirmasiPembayaran($pembayaran);
```

### 4. Notifikasi Sistem
```php
$whatsapp->sendSystemNotification(
    '08123456789',
    'Maintenance Sistem',
    'Sistem akan down untuk maintenance pada pukul 23:00 WIB',
    'maintenance'
);
```

### 5. Pesan dengan File
```php
$whatsapp->sendMessageWithFile(
    '08123456789',
    'Berikut adalah bukti pembayaran Anda',
    storage_path('app/bukti_pembayaran.pdf'),
    'Bukti_Pembayaran_SPP.pdf'
);
```

### 6. Pesan dengan URL
```php
$whatsapp->sendMessageWithUrl(
    '08123456789',
    'Berikut adalah link bukti pembayaran',
    'https://example.com/bukti_pembayaran.pdf'
);
```

## Tipe Notifikasi Sistem

- `info`: Informasi umum (ℹ️)
- `success`: Berhasil (✅)
- `warning`: Peringatan (⚠️)
- `error`: Error (❌)
- `maintenance`: Maintenance (🔧)
- `update`: Update (🔄)

## Integrasi dengan Notification Laravel

Untuk mengintegrasikan dengan sistem notification Laravel, tambahkan channel WhatsApp:

```php
// app/Notifications/PembayaranNotification.php
public function via(object $notifiable): array
{
    return ['database', 'whatsapp'];
}

public function toWhatsapp(object $notifiable)
{
    $whatsapp = new WhatsappFonnteServices();
    return $whatsapp->sendPembayaranNotification($this->pembayaran);
}
```

## Troubleshooting

### 1. Token Invalid
- Pastikan token Fonnte valid dan aktif
- Cek status device di dashboard Fonnte

### 2. Nomor WhatsApp Tidak Valid
- Pastikan format nomor benar (contoh: 08123456789)
- Cek apakah nomor terdaftar di WhatsApp

### 3. Notifikasi Tidak Terkirim
- Cek log Laravel di `storage/logs/laravel.log`
- Pastikan semua variabel environment sudah diset dengan benar
- Cek apakah device Fonnte terhubung

### 4. File Tidak Terkirim
- Pastikan file tidak lebih dari 4MB
- Format file harus didukung (PDF, JPG, PNG, MP4, dll)
- URL file harus publik dan dapat diakses

## Monitoring

Semua aktivitas WhatsApp akan di-log di `storage/logs/laravel.log` dengan level:
- `info`: Pesan berhasil dikirim
- `warning`: Wali tidak memiliki nomor WhatsApp
- `error`: Gagal mengirim pesan

## Keamanan

- Jangan pernah commit token Fonnte ke repository
- Gunakan environment variable untuk semua konfigurasi sensitif
- Batasi akses ke service WhatsApp hanya untuk user yang berwenang 