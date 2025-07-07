# Invoice System Documentation

Dokumentasi untuk sistem invoice yang telah diperbaiki dan ditingkatkan.

## Fitur Utama

### 1. **Tampilan PDF Otomatis**
- Invoice langsung ditampilkan dalam format PDF di browser
- Tidak perlu klik tombol untuk melihat PDF
- Pengalaman pengguna yang lebih baik

### 2. **Desain Responsif**
- Tampilan yang optimal di desktop, tablet, dan mobile
- CSS Grid dan Flexbox untuk layout yang fleksibel
- Media queries untuk berbagai ukuran layar

### 3. **Desain Modern**
- Menggunakan font Inter untuk tipografi yang modern
- Gradient background di header
- Shadow dan border radius untuk efek visual
- Color scheme yang konsisten

## Cara Penggunaan

### 1. **Melihat Invoice (Default - PDF)**
```
GET /walimurid/invoice/{id}
```
- Menampilkan invoice dalam format PDF di browser
- Contoh: `/walimurid/invoice/123`

### 2. **Melihat Invoice HTML**
```
GET /walimurid/invoice/{id}/html
```
- Menampilkan invoice dalam format HTML
- Berguna untuk preview atau debugging
- Contoh: `/walimurid/invoice/123/html`

### 3. **Download PDF**
```
GET /walimurid/invoice/{id}?download=true
```
- Download file PDF ke komputer
- Nama file: `invoice_tagihan_{nama_siswa}.pdf`

## Struktur File

### Controller
- `app/Http/Controllers/WaliMuridInvoiceController.php`
  - Method `show()` untuk menangani semua request invoice
  - Logic untuk PDF, HTML, dan download

### View
- `resources/views/wali/invoice_tagihan.blade.php`
  - Template HTML untuk invoice
  - Menggunakan CSS eksternal

### CSS
- `public/css/invoice.css`
  - Semua styling untuk invoice
  - Responsive design
  - Print styles

### Routes
```php
// Di routes/web.php
Route::get('invoice/{id}', [WaliMuridInvoiceController::class, 'show'])->name('invoice.show');
Route::get('invoice/{id}/html', [WaliMuridInvoiceController::class, 'show'])->name('invoice.show.html');
```

## Komponen Invoice

### 1. **Header**
- Logo instansi (jika ada)
- Nama dan informasi instansi
- Nomor invoice dan tanggal
- Gradient background

### 2. **Informasi Siswa & Wali**
- Data lengkap siswa (nama, NISN, NIS, kelas)
- Data wali murid (nama, email, no HP)
- Layout grid yang responsif

### 3. **Tabel Tagihan**
- Daftar semua tagihan siswa
- Status pembayaran dengan badge berwarna
- Total tagihan di footer tabel

### 4. **Tanda Tangan**
- Tanda tangan bendahara
- Nama dan NIP bendahara
- Tanggal dan tempat

## Status Pembayaran

### Badge Warna
- **Hijau (Lunas)**: Pembayaran sudah lunas
- **Kuning (Belum Lunas)**: Belum ada pembayaran
- **Biru (Sebagian)**: Sudah bayar sebagian

## Responsive Design

### Breakpoints
- **Desktop**: > 768px
- **Tablet**: 768px - 480px
- **Mobile**: < 480px

### Adaptasi Mobile
- Header menjadi vertikal
- Grid info menjadi 1 kolom
- Font size menyesuaikan
- Padding dan margin berkurang

## Print Styles

### CSS Print
```css
@media print {
    .button-group { display: none; }
    .invoice-container { box-shadow: none; }
    .invoice-header { background: #374151 !important; }
}
```

### Fitur Print
- Tombol disembunyikan saat print
- Background gradient tetap terlihat
- Layout optimal untuk kertas A4

## Helper Functions

### Logo Instansi
```php
$logoUrl = getInstansiLogoUrl();
if ($logoUrl) {
    echo '<img src="' . $logoUrl . '" alt="Logo">';
}
```

### Format Rupiah
```php
{{ formatRupiah($total) }}
```

### Format Tanggal
```php
{{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM Y') }}
```

## Error Handling

### Logo Tidak Ada
- Helper function `getInstansiLogoUrl()` menangani file yang tidak ada
- Return string kosong jika file tidak ditemukan
- Tidak menyebabkan error

### Data Kosong
- Menggunakan null coalescing operator (`??`)
- Fallback value untuk data yang kosong
- Tampilan tetap rapi meski data tidak lengkap

## Performance

### Optimasi
- CSS eksternal untuk caching
- Font Inter dari Google Fonts (CDN)
- Minimal inline styles
- Efficient DOM structure

### Loading
- CSS dan font di-load secara paralel
- Tidak ada blocking resources
- Fast rendering di semua device

## Browser Support

### Modern Browsers
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

### Features
- CSS Grid
- Flexbox
- CSS Custom Properties
- Modern JavaScript

## Maintenance

### CSS Updates
- Semua styling di `public/css/invoice.css`
- Mudah untuk update dan maintain
- Tidak perlu edit template HTML

### Template Updates
- Logic terpisah dari styling
- Struktur HTML yang clean
- Mudah untuk menambah fitur baru

## Troubleshooting

### PDF Tidak Muncul
1. Cek apakah package `barryvdh/laravel-dompdf` terinstall
2. Cek permission folder storage
3. Cek log Laravel untuk error

### Logo Tidak Muncul
1. Cek apakah file logo ada di storage
2. Cek setting `logo_instansi` di database
3. Cek permission file logo

### Layout Rusak di Mobile
1. Cek viewport meta tag
2. Cek CSS media queries
3. Test di berbagai device

## Future Enhancements

### Fitur yang Bisa Ditambah
- Watermark di PDF
- QR Code untuk pembayaran
- Multiple language support
- Dark mode
- Custom themes
- Email integration 