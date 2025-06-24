# Fitur Pembayaran Wali Murid

## Deskripsi

Fitur pembayaran untuk wali murid yang memungkinkan wali melakukan pembayaran tagihan siswa melalui modal form.

## Fitur

-   ✅ Tombol bayar muncul hanya jika status tagihan belum lunas
-   ✅ Modal form pembayaran dengan pilihan item tagihan
-   ✅ Auto-fill jumlah pembayaran berdasarkan sisa tagihan
-   ✅ Upload bukti pembayaran untuk transfer bank
-   ✅ Validasi jumlah pembayaran
-   ✅ Update status tagihan otomatis
-   ✅ Feedback sukses/error
-   ✅ **Total tagihan ditampilkan dengan benar**

## File yang Dimodifikasi

### Controller

-   `app/Http/Controllers/WaliMuridTagihanController.php` - Menambahkan method getDetails dengan format data yang diperbaiki

### Views

-   `resources/views/wali/tagihan_index.blade.php` - Menambahkan tombol bayar dan modal dengan JavaScript yang robust

### Routes

-   `routes/web.php` - Menambahkan route untuk details dan pembayaran wali

## Perbaikan Masalah

### Masalah Total Tagihan Tidak Muncul

**Penyebab:**

-   Format data yang tidak konsisten antara controller dan JavaScript
-   Tidak ada validasi untuk data yang undefined/null
-   Element HTML tidak ditemukan

**Solusi:**

1. **Controller:** Memastikan data dikirim dengan format yang konsisten

    ```php
    'total_tagihan' => (float) $totalTagihan,
    'jumlah_biaya' => (float) $detail->jumlah_biaya,
    ```

2. **JavaScript:** Menambahkan validasi robust

    ```javascript
    if (totalTagihanElement) {
        if (data.total_tagihan !== undefined && data.total_tagihan !== null) {
            totalTagihanElement.value = formatRupiah(data.total_tagihan);
        } else {
            totalTagihanElement.value = "Rp 0";
        }
    }
    ```

3. **Debug Logging:** Menambahkan console.log untuk debugging
    ```javascript
    console.log("Data received:", data);
    console.log("Total tagihan:", data.total_tagihan);
    ```

## Cara Kerja

### 1. Tombol Bayar

-   Tombol "Bayar" hanya muncul jika status tagihan bukan "lunas"
-   Status dihitung berdasarkan jumlah item yang sudah lunas vs total item

### 2. Modal Form Pembayaran

-   Modal menampilkan form pembayaran dengan informasi siswa
-   **Total tagihan ditampilkan dengan format Rupiah**
-   Menampilkan daftar item tagihan yang belum lunas
-   User dapat memilih item yang akan dibayar

### 3. Auto-fill Jumlah

-   Jumlah pembayaran otomatis terisi sesuai sisa tagihan item yang dipilih
-   User dapat mengubah jumlah (tidak boleh melebihi sisa)

### 4. Upload Bukti

-   Field bukti pembayaran muncul jika metode "Bank Transfer" dipilih
-   Menerima file gambar (jpg, jpeg, png) atau PDF
-   Maksimal ukuran 2MB

### 5. Validasi

-   Jumlah pembayaran tidak boleh melebihi sisa tagihan
-   Bukti pembayaran wajib untuk transfer bank
-   Semua field wajib diisi

## Struktur Data

### Request Pembayaran

```json
{
    "tagihan_id": "1",
    "detail_id": "5",
    "siswa_id": "10",
    "jumlah_dibayar": "500000",
    "metode_pembayaran": "Bank Transfer",
    "tanggal_bayar": "2024-01-15",
    "status_konfirmasi": "Belum Dikonfirmasi",
    "bukti_bayar": "file"
}
```

### Response Sukses

```json
{
    "success": true,
    "message": "Pembayaran berhasil disimpan",
    "data": {
        "pembayaran": {...},
        "detail": {...},
        "sisa_tagihan": 0
    }
}
```

### Response Tagihan Details

```json
{
    "success": true,
    "details": [...],
    "total_tagihan": 1500000.0,
    "siswa": {
        "id": 10,
        "nama": "Nama Siswa"
    }
}
```

## Status Tagihan

### Perhitungan Status

-   **Lunas**: Semua item tagihan sudah lunas
-   **Diangsur**: Ada item yang sudah dibayar sebagian
-   **Belum Lunas**: Ada item yang belum dibayar sama sekali
-   **Baru**: Tagihan baru dibuat

### Update Status Otomatis

-   Setelah pembayaran, status item tagihan diupdate otomatis
-   Jika total bayar >= jumlah biaya → status = "lunas"
-   Jika total bayar > 0 → status = "angsur"
-   Jika total bayar = 0 → status = "belum_lunas"

## Keamanan

### Authorization

-   Hanya wali yang dapat mengakses pembayaran siswa mereka
-   Validasi siswa_id untuk memastikan siswa milik wali tersebut

### File Upload

-   Validasi tipe file (gambar/PDF)
-   Validasi ukuran file (max 2MB)
-   File disimpan di storage public dengan nama unik

### Database Transaction

-   Menggunakan database transaction untuk konsistensi data
-   Rollback otomatis jika terjadi error

## URL Routes

### Wali Routes

-   `GET /walimurid/tagihan` - Index tagihan wali
-   `GET /walimurid/tagihan/{id}` - Show detail tagihan
-   `GET /walimurid/tagihan/{id}/details` - Get tagihan details (JSON)
-   `POST /walimurid/pembayaran/store` - Store pembayaran

## JavaScript Functions

### openPaymentModal(tagihanId, siswaNama)

-   Membuka modal pembayaran
-   Fetch data tagihan details
-   Populate form dengan data yang sesuai
-   **Menampilkan total tagihan dengan format Rupiah**

### initializePaymentForm()

-   Setup event listeners untuk form
-   Handle form submission dengan AJAX
-   Validasi input dan feedback

### formatRupiah(amount)

-   Helper function untuk format currency Indonesia

## Error Handling

### Client-side

-   Validasi input sebelum submit
-   Feedback error melalui alert di modal
-   Disable submit button saat processing
-   **Validasi data sebelum menampilkan total tagihan**

### Server-side

-   Validasi request data
-   Database transaction rollback jika error
-   Response JSON dengan pesan error

## Integrasi dengan Sistem

### Model Relationships

-   `Pembayaran` → `TagihanDetail` → `Tagihan` → `Siswa` → `Wali`
-   `Pembayaran` → `User` (admin/operator yang konfirmasi)

### Status Flow

1. Wali memilih item tagihan
2. Wali mengisi form pembayaran
3. Sistem validasi dan simpan pembayaran
4. Update status tagihan detail
5. Feedback sukses dan reload halaman

## Testing

### Manual Testing

1. Login sebagai wali
2. Buka halaman tagihan
3. Klik tombol "Bayar" pada tagihan yang belum lunas
4. **Verifikasi total tagihan muncul dengan benar**
5. Pilih item tagihan
6. Isi form pembayaran
7. Submit dan verifikasi hasil

### Edge Cases

-   Pembayaran melebihi sisa tagihan
-   Upload file tidak valid
-   Network error saat fetch data
-   Tagihan sudah lunas (tombol tidak muncul)
-   **Data tagihan kosong atau null**
