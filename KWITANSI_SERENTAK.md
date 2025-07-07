# Fitur Kwitansi Pembayaran Serentak

## Deskripsi
Fitur ini memungkinkan operator untuk menampilkan kwitansi langsung setelah melakukan pembayaran serentak (batch payment). Kwitansi akan menampilkan semua item yang dibayar dalam satu dokumen.

## Cara Kerja

### 1. Pembayaran Serentak
- Operator memilih beberapa item tagihan untuk dibayar sekaligus
- Sistem membuat multiple record pembayaran untuk setiap item
- Setelah pembayaran berhasil, sistem otomatis membuka kwitansi serentak

### 2. Kwitansi Serentak
- Menampilkan semua item yang dibayar dalam satu tabel
- Menghitung total pembayaran dari semua item
- Menampilkan informasi siswa dan periode pembayaran
- Tersedia dalam format HTML dan PDF

## File yang Terlibat

### Views
- `resources/views/operator/kwitansi_pembayaran_serentak.blade.php` - Template kwitansi serentak

### Controllers
- `app/Http/Controllers/KwitansiPembayaranController.php` - Method `showBatch()`

### Routes
- `POST /operator/kwitansi/batch` - Menampilkan kwitansi serentak
- `GET /operator/kwitansi/batch/pdf` - Download PDF kwitansi serentak

### JavaScript
- `resources/views/operator/tagihan_siswa_detail.blade.php` - Function `showBatchKwitansi()`

## Fitur Kwitansi Serentak

### Informasi yang Ditampilkan
1. **Header Instansi**
   - Logo instansi (jika ada)
   - Nama dan alamat instansi

2. **Informasi Transaksi**
   - Nomor transaksi (ID pembayaran pertama)
   - Tanggal dan waktu pembayaran
   - Data siswa (NISN, kelas, nama)
   - Metode pembayaran

3. **Tabel Item Pembayaran**
   - Nomor urut
   - Nama pembayaran (jenis biaya)
   - Periode (bulan dan tahun)
   - Nominal pembayaran

4. **Total Pembayaran**
   - Total keseluruhan
   - Metode pembayaran
   - Kembalian (selalu 0)

5. **Tanda Tangan**
   - Tanggal dan tempat
   - Nama petugas

### Format Output
- **HTML**: Tampilan web dengan tombol download PDF dan cetak
- **PDF**: Dokumen yang bisa didownload atau dicetak

## Cara Penggunaan

### Untuk Operator
1. Buka halaman detail tagihan siswa
2. Klik tombol "Bayar Serentak"
3. Pilih item yang akan dibayar dengan checkbox
4. Isi form pembayaran
5. Klik "Simpan Pembayaran"
6. Kwitansi serentak akan otomatis terbuka di tab baru

### Tombol yang Tersedia
- **Download PDF**: Mengunduh kwitansi dalam format PDF
- **Cetak**: Mencetak kwitansi langsung dari browser

## Validasi

### Keamanan
- Hanya operator yang bisa mengakses
- Validasi bahwa semua pembayaran untuk siswa yang sama
- Validasi ID pembayaran yang valid

### Data
- Memastikan semua pembayaran ditemukan
- Memastikan semua pembayaran untuk siswa yang sama
- Update status tagihan detail jika pembayaran dikonfirmasi

## Error Handling

### Error yang Ditangani
1. **Data tidak ditemukan**: Jika ID pembayaran tidak valid
2. **Pembayaran berbeda siswa**: Jika ada pembayaran untuk siswa lain
3. **Database error**: Jika terjadi error saat query data

### Logging
- Semua error dicatat dalam log Laravel
- Informasi error ditampilkan ke user

## Contoh Output

```
BUKTI PEMBAYARAN SERENTAK

No Transaksi: 123
Tanggal: 15-12-2024 14:30:25
No Induk: 12345678
Kelas: XI
Nama: Ahmad Siswa
Metode: Cash

┌─────┬─────────────────────┬──────────┬─────────────┐
│ No  │ Nama Pembayaran     │ Periode  │ Nominal     │
├─────┼─────────────────────┼──────────┼─────────────┤
│ 1   │ SPP                 │ Jan 2024 │ 500.000     │
│ 2   │ Uang Makan          │ Jan 2024 │ 200.000     │
│ 3   │ SPP                 │ Feb 2024 │ 500.000     │
└─────┴─────────────────────┴──────────┴─────────────┘

Total : 1.200.000
Cash : 1.200.000
Kembali : 0

SMA Negeri 1, 15-12-2024
Petugas

[TTD]
Nama Petugas
```

## Troubleshooting

### Kwitansi tidak muncul
1. Periksa console browser untuk error JavaScript
2. Periksa log Laravel untuk error server
3. Pastikan pembayaran berhasil disimpan

### PDF tidak terdownload
1. Periksa konfigurasi DomPDF
2. Pastikan library PDF terinstall
3. Periksa permission folder storage

### Data tidak lengkap
1. Periksa relasi model Pembayaran
2. Pastikan eager loading berfungsi
3. Periksa data di database 