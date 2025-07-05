# Sistem Pengaturan Instansi

## Deskripsi
Sistem pengaturan instansi memungkinkan administrator untuk mengelola informasi dasar instansi seperti nama, email, nomor WhatsApp, dan alamat. Data disimpan dalam database untuk persistensi yang lebih baik.

## Struktur Database

### Tabel: `instansi_settings`
- `id` - Primary key
- `nama_instansi` - Nama instansi (string, nullable)
- `email_instansi` - Email instansi (string, nullable) 
- `nomor_wa_instansi` - Nomor WhatsApp instansi (string, nullable)
- `alamat_instansi` - Alamat instansi (text, nullable)
- `created_at` - Timestamp pembuatan
- `updated_at` - Timestamp update

## Model: `App\Models\Setting`

### Method yang Tersedia:
1. `getInstansiSettings()` - Mendapatkan pengaturan instansi (record pertama atau instance baru)
2. `saveInstansiSettings($data)` - Menyimpan atau memperbarui pengaturan instansi

### Contoh Penggunaan:
```php
// Mendapatkan semua pengaturan
$settings = Setting::getInstansiSettings();

// Mendapatkan nama instansi
$namaInstansi = $settings->nama_instansi;

// Menyimpan pengaturan baru
Setting::saveInstansiSettings([
    'nama_instansi' => 'SMA Negeri 1',
    'email_instansi' => 'info@sman1.sch.id',
    'nomor_wa_instansi' => '081234567890',
    'alamat_instansi' => 'Jl. Pendidikan No. 123'
]);
```

## Helper Function

### `getInstansiSetting($key = null)`
Function helper untuk mengakses pengaturan instansi dari mana saja.

#### Contoh Penggunaan:
```php
// Mendapatkan semua pengaturan
$settings = getInstansiSetting();

// Mendapatkan nama instansi
$namaInstansi = getInstansiSetting('nama_instansi');

// Mendapatkan email instansi
$emailInstansi = getInstansiSetting('email_instansi');
```

## Controller: `App\Http\Controllers\SettingController`

### Method yang Tersedia:
1. `index()` - Menampilkan form pengaturan
2. `store(Request $request)` - Menyimpan pengaturan

### Validasi:
- `nama_instansi`: required, string, max 255 karakter
- `email_instansi`: required, email, max 255 karakter
- `nomor_wa_instansi`: required, string, max 20 karakter
- `alamat_instansi`: required, string, max 500 karakter

## Routes

### Operator Routes:
- `GET /operator/setting` - Menampilkan form pengaturan
- `POST /operator/setting` - Menyimpan pengaturan

## View: `resources/views/operator/setting_form.blade.php`

Form pengaturan instansi dengan validasi client-side dan server-side.

### Fitur:
- Input validation
- Format nomor WhatsApp (hanya angka)
- Success/error messages
- Responsive design

## Seeder: `InstansiSettingSeeder`

Seeder untuk mengisi data awal pengaturan instansi.

### Data Default:
- Nama: "Sekolah Menengah Atas Negeri 1"
- Email: "info@sman1.sch.id"
- WhatsApp: "081234567890"
- Alamat: "Jl. Pendidikan No. 123, Kota Pendidikan, Provinsi Pendidikan"

## Cara Menjalankan

1. **Migration:**
   ```bash
   php artisan migrate
   ```

2. **Seeder:**
   ```bash
   php artisan db:seed --class=InstansiSettingSeeder
   ```

3. **Akses:**
   - Login sebagai operator
   - Kunjungi `/operator/setting`

## Keuntungan Menggunakan Database

1. **Persistensi Data** - Data tidak hilang saat server restart
2. **Backup & Restore** - Mudah di-backup bersama database
3. **Version Control** - Perubahan dapat dilacak
4. **Multi-User** - Aman untuk aplikasi multi-user
5. **Scalability** - Mudah dikembangkan untuk fitur tambahan

## Migrasi dari Cache

Sistem ini menggantikan penyimpanan cache dengan database. Perubahan utama:

1. **Controller** - Menggunakan model `Setting` instead of `Cache`
2. **View** - Mengakses data sebagai object instead of array
3. **Helper** - Function `getInstansiSetting()` untuk akses global

## Pengembangan Selanjutnya

1. **Logo Instansi** - Tambah field untuk upload logo
2. **Social Media** - Tambah field untuk social media links
3. **Working Hours** - Tambah field untuk jam operasional
4. **Multiple Settings** - Support untuk multiple instansi
5. **Audit Trail** - Log perubahan pengaturan 