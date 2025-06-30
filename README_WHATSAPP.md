# Fitur WhatsApp Notification SPP

Sistem notifikasi WhatsApp menggunakan Fonnte API untuk sistem pembayaran SPP sekolah.

## 🚀 Fitur Utama

### 1. **Notifikasi Pembayaran** 💰
- Otomatis dikirim ketika ada pembayaran baru
- Berisi detail pembayaran: siswa, jumlah, metode, tanggal
- Format pesan yang profesional dan informatif

### 2. **Reminder Pembayaran** ⏰
- Pengingat otomatis untuk tagihan yang akan jatuh tempo
- Dapat dikonfigurasi berapa hari sebelum jatuh tempo
- Mencegah keterlambatan pembayaran

### 3. **Konfirmasi Pembayaran** ✅
- Konfirmasi ketika pembayaran berhasil diproses
- Memberikan kepastian kepada wali murid
- Status pembayaran yang jelas

### 4. **Notifikasi Sistem** 🔔
- Notifikasi umum untuk maintenance, update, dll
- Dapat dikirim ke semua wali murid sekaligus
- Berbagai tipe: info, success, warning, error, maintenance, update

## 📋 Persyaratan

1. **Akun Fonnte** - Daftar di [fonnte.com](https://fonnte.com)
2. **Token API** - Dapatkan dari dashboard Fonnte
3. **Device Terhubung** - Pastikan device WhatsApp terhubung di Fonnte
4. **Nomor Wali** - Wali murid harus memiliki nomor WhatsApp

## ⚙️ Instalasi & Konfigurasi

### 1. Setup Environment Variables

Tambahkan ke file `.env`:

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

### 2. Register Commands

Pastikan commands sudah terdaftar di `app/Console/Kernel.php`:

```php
protected $commands = [
    \App\Console\Commands\SendReminderPembayaran::class,
    \App\Console\Commands\TestWhatsappService::class,
];
```

### 3. Setup Cron Job (Opsional)

Untuk reminder otomatis, tambahkan ke crontab:

```bash
# Kirim reminder setiap hari jam 9 pagi
0 9 * * * cd /path/to/your/project && php artisan whatsapp:send-reminder --days=7
```

## 🛠️ Cara Penggunaan

### Command Line

#### Test WhatsApp Service
```bash
# Test pesan sistem
php artisan whatsapp:test 08123456789 --type=system --title="Test" --message="Ini pesan test"

# Test notifikasi pembayaran
php artisan whatsapp:test 08123456789 --type=pembayaran

# Test reminder pembayaran
php artisan whatsapp:test 08123456789 --type=reminder

# Test konfirmasi pembayaran
php artisan whatsapp:test 08123456789 --type=konfirmasi
```

#### Kirim Reminder
```bash
# Kirim reminder untuk tagihan yang jatuh tempo dalam 7 hari
php artisan whatsapp:send-reminder --days=7

# Mode test (tidak benar-benar mengirim)
php artisan whatsapp:send-reminder --days=7 --test
```

### Programmatic Usage

#### 1. Notifikasi Pembayaran
```php
use App\Services\WhatsappFonnteServices;

$whatsapp = new WhatsappFonnteServices();
$whatsapp->sendPembayaranNotification($pembayaran);
```

#### 2. Reminder Pembayaran
```php
$whatsapp->sendReminderPembayaran($tagihan);
```

#### 3. Konfirmasi Pembayaran
```php
$whatsapp->sendKonfirmasiPembayaran($pembayaran);
```

#### 4. Notifikasi Sistem
```php
$whatsapp->sendSystemNotification(
    '08123456789',
    'Maintenance Sistem',
    'Sistem akan down untuk maintenance pada pukul 23:00 WIB',
    'maintenance'
);
```

#### 5. Pesan dengan File
```php
$whatsapp->sendMessageWithFile(
    '08123456789',
    'Berikut adalah bukti pembayaran Anda',
    storage_path('app/bukti_pembayaran.pdf'),
    'Bukti_Pembayaran_SPP.pdf'
);
```

#### 6. Pesan dengan URL
```php
$whatsapp->sendMessageWithUrl(
    '08123456789',
    'Berikut adalah link bukti pembayaran',
    'https://example.com/bukti_pembayaran.pdf'
);
```

## 🔧 Konfigurasi Dinamis

### Mengaktifkan/Nonaktifkan Fitur

```php
// Nonaktifkan notifikasi pembayaran
config(['services.fonnte.notifications.pembayaran' => false]);

// Aktifkan kembali
config(['services.fonnte.notifications.pembayaran' => true]);
```

### Pengaturan Runtime

```php
use App\Services\WhatsappFonnteServices;

$whatsapp = new WhatsappFonnteServices();

// Set delay
$whatsapp->setDelay('5');

// Set typing indicator
$whatsapp->setTyping(true);

// Set country code
$whatsapp->setCountryCode('62');
```

## 📱 Format Pesan

### Notifikasi Pembayaran
```
💰 *NOTIFIKASI PEMBAYARAN SPP*

Halo [Nama Wali],

Pembayaran SPP telah diterima:
• Siswa: [Nama Siswa]
• Kelas: [Kelas]
• Jumlah: Rp [Jumlah]
• Metode: [Metode Pembayaran]
• Tanggal: [Tanggal]

Terima kasih atas pembayaran Anda.
Semoga pendidikan anak Anda berjalan lancar! 📚✨
```

### Reminder Pembayaran
```
⏰ *PENGINGAT PEMBAYARAN SPP*

Halo [Nama Wali],

Ini adalah pengingat untuk pembayaran SPP:
• Siswa: [Nama Siswa]
• Kelas: [Kelas]
• Total Tagihan: Rp [Total]
• Tenggat Waktu: [Tanggal]

Mohon segera lakukan pembayaran untuk menghindari keterlambatan.
Terima kasih atas perhatiannya! 🙏
```

### Konfirmasi Pembayaran
```
✅ *KONFIRMASI PEMBAYARAN SPP*

Halo [Nama Wali],

Pembayaran SPP telah berhasil dikonfirmasi:
• Siswa: [Nama Siswa]
• Kelas: [Kelas]
• Jumlah: Rp [Jumlah]
• Tanggal: [Tanggal]
• Status: ✅ LUNAS

Pembayaran telah diproses dan diterima dengan baik.
Terima kasih! 🎉
```

## 🔍 Monitoring & Logging

### Log Files
Semua aktivitas WhatsApp di-log di `storage/logs/laravel.log`:

```php
// Success
[2024-01-15 10:30:00] local.INFO: WhatsApp message sent successfully {
    "target": "08123456789",
    "message_id": "80367170",
    "request_id": "2937124"
}

// Warning
[2024-01-15 10:30:00] local.WARNING: Wali tidak memiliki nomor WhatsApp {
    "siswa_id": 123
}

// Error
[2024-01-15 10:30:00] local.ERROR: WhatsApp message failed {
    "target": "08123456789",
    "response": {"status": false, "reason": "token invalid"}
}
```

### Dashboard Monitoring
- Total pesan terkirim
- Tingkat keberhasilan
- Error rate
- Quota usage

## 🚨 Troubleshooting

### 1. Token Invalid
**Gejala**: Error "token invalid"
**Solusi**:
- Cek token di dashboard Fonnte
- Pastikan token aktif dan tidak expired
- Restart device jika perlu

### 2. Device Tidak Terhubung
**Gejala**: Error "device disconnected"
**Solusi**:
- Cek status device di dashboard Fonnte
- Scan QR code ulang jika perlu
- Pastikan device tidak logout

### 3. Nomor Tidak Valid
**Gejala**: Error "target invalid"
**Solusi**:
- Pastikan format nomor benar (08123456789)
- Cek apakah nomor terdaftar di WhatsApp
- Pastikan country code sesuai

### 4. File Tidak Terkirim
**Gejala**: Error "file format not supported"
**Solusi**:
- Pastikan file < 4MB
- Format yang didukung: PDF, JPG, PNG, MP4, MP3
- URL file harus publik

### 5. Quota Habis
**Gejala**: Error "insufficient quota"
**Solusi**:
- Cek quota di dashboard Fonnte
- Upgrade paket jika perlu
- Tunggu reset quota bulanan

## 🔒 Keamanan

### Best Practices
1. **Jangan commit token** ke repository
2. **Gunakan environment variables** untuk semua konfigurasi sensitif
3. **Batasi akses** ke service WhatsApp hanya untuk user yang berwenang
4. **Monitor log** secara berkala untuk aktivitas mencurigakan
5. **Rotate token** secara berkala

### Rate Limiting
- Delay default: 2 detik antar pesan
- Maksimal 1000 pesan per hari (tergantung paket)
- Jangan spam untuk menghindari blocking

## 📞 Support

### Dokumentasi Fonnte
- [API Documentation](https://docs.fonnte.com/api-send-message/)
- [Dashboard Fonnte](https://fonnte.com/dashboard)
- [Status Service](https://status.fonnte.com)

### Contact
- Email: support@fonnte.com
- WhatsApp: +62 812-3456-7890
- Telegram: @fonnte_support

## 📝 Changelog

### v1.0.0 (2024-01-15)
- ✅ Fitur notifikasi pembayaran
- ✅ Fitur reminder pembayaran
- ✅ Fitur konfirmasi pembayaran
- ✅ Fitur notifikasi sistem
- ✅ Support file attachment
- ✅ Support URL attachment
- ✅ Command line tools
- ✅ Dynamic configuration
- ✅ Comprehensive logging
- ✅ Error handling
- ✅ Security features

---

**Dibuat dengan ❤️ untuk sistem SPP sekolah yang lebih baik** 