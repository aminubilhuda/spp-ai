# CRUD Bank Sekolah

## Deskripsi

CRUD (Create, Read, Update, Delete) untuk mengelola data bank sekolah yang digunakan untuk pembayaran SPP.

## Fitur

-   ✅ Menampilkan daftar bank sekolah
-   ✅ Menambah bank sekolah baru
-   ✅ Mengedit data bank sekolah
-   ✅ Menampilkan detail bank sekolah
-   ✅ Menghapus bank sekolah
-   ✅ Validasi input
-   ✅ Pagination
-   ✅ Flash messages untuk feedback
-   ✅ **Dropdown bank dari tabel banks**
-   ✅ **Auto-fill kode bank dan nama bank**

## File yang Dibuat/Dimodifikasi

### Controller

-   `app/Http/Controllers/BankSekolahController.php` - Controller utama untuk CRUD

### Request Validation

-   `app/Http/Requests/StoreBankSekolahRequest.php` - Validasi untuk create
-   `app/Http/Requests/UpdateBankSekolahRequest.php` - Validasi untuk update

### Views (Operator)

-   `resources/views/operator/bank_sekolah_index.blade.php` - Halaman daftar
-   `resources/views/operator/bank_sekolah_form.blade.php` - Form create/edit
-   `resources/views/operator/bank_sekolah_show.blade.php` - Halaman detail

### Model

-   `app/Models/BankSekolah.php` - Model dengan fillable fields

### Database

-   `database/migrations/2025_06_23_234753_create_bank_sekolahs_table.php` - Migration tabel
-   `database/factories/BankSekolahFactory.php` - Factory untuk testing
-   `database/seeders/BankSekolahSeeder.php` - Seeder dengan data sample

### Policy

-   `app/Policies/BankSekolahPolicy.php` - Authorization policy

### Routes

-   `routes/web.php` - Route resource untuk bank-sekolah

### Menu

-   `resources/views/layouts/menu.blade.php` - Menu navigasi

## Struktur Database

Tabel `bank_sekolahs` memiliki kolom:

-   `id` - Primary key
-   `kode_bank` - Kode bank (unique)
-   `nama_bank` - Nama bank
-   `no_rekening` - Nomor rekening
-   `atas_nama` - Atas nama rekening (nullable)
-   `keterangan` - Keterangan tambahan (nullable)
-   `created_at` - Timestamp created
-   `updated_at` - Timestamp updated

## Cara Menggunakan

### 1. Akses Menu

Login sebagai operator dan klik menu "Rekening Sekolah" di sidebar.

### 2. Menambah Bank Sekolah

1. Klik tombol "Tambah Bank Sekolah"
2. **Pilih bank dari dropdown** (nama bank akan ditampilkan)
3. **Kode bank dan nama bank akan terisi otomatis**
4. Isi nomor rekening, atas nama, dan keterangan
5. Klik "SIMPAN"

### 3. Mengedit Bank Sekolah

1. Klik icon edit (pensil) pada baris data
2. **Pilih bank dari dropdown** (bank yang sudah dipilih akan ter-select)
3. **Kode bank dan nama bank akan terisi otomatis**
4. Ubah data yang diperlukan
5. Klik "UPDATE"

### 4. Melihat Detail

1. Klik icon view (mata) pada baris data
2. Data detail akan ditampilkan

### 5. Menghapus Bank Sekolah

1. Klik icon delete (tempat sampah) pada baris data
2. Konfirmasi penghapusan

## Validasi

-   **Pilih bank wajib diisi**
-   Kode bank wajib diisi dan harus unik (auto-fill dari tabel banks)
-   Nama bank wajib diisi (auto-fill dari tabel banks)
-   Nomor rekening wajib diisi
-   Atas nama dan keterangan bersifat opsional

## Integrasi dengan Tabel Banks

-   Form menggunakan dropdown yang mengambil data dari tabel `banks`
-   Field `nama_bank` menampilkan pilihan bank
-   Field `kode_bank` dan `nama_bank` terisi otomatis berdasarkan pilihan
-   Data diambil dari kolom `nama_bank` dan `sandi_bank` di tabel `banks`

## Keamanan

-   Hanya operator yang dapat mengakses CRUD ini
-   Menggunakan policy untuk authorization
-   Validasi input untuk mencegah data yang tidak valid
-   Validasi bank_id untuk memastikan bank yang dipilih valid

## Data Sample

Seeder akan membuat 3 data sample:

1. Bank BCA - Rekening utama sekolah
2. Bank Mandiri - Rekening cadangan
3. Bank BNI - Rekening khusus SPP

## URL Routes

-   `GET /operator/bank-sekolah` - Index (daftar)
-   `GET /operator/bank-sekolah/create` - Form create
-   `POST /operator/bank-sekolah` - Store
-   `GET /operator/bank-sekolah/{id}` - Show
-   `GET /operator/bank-sekolah/{id}/edit` - Form edit
-   `PUT /operator/bank-sekolah/{id}` - Update
-   `DELETE /operator/bank-sekolah/{id}` - Destroy
