# Helper Functions Documentation

Dokumentasi untuk helper functions yang tersedia di aplikasi SPP AI.

## Format Functions

### `formatRupiah($nominal, $prefix = null)`
Mengubah angka menjadi format mata uang Rupiah.

**Parameters:**
- `$nominal` (int/float): Angka yang akan diformat
- `$prefix` (string, optional): Prefix untuk mata uang (default: 'Rp. ')

**Returns:** string

**Example:**
```php
echo formatRupiah(1000000); // Output: Rp. 1.000.000
echo formatRupiah(1000000, 'IDR '); // Output: IDR 1.000.000
```

### `formatTanggalIndonesia($tanggal, $format = 'd F Y')`
Mengubah tanggal menjadi format Indonesia.

**Parameters:**
- `$tanggal` (string/DateTime): Tanggal yang akan diformat
- `$format` (string, optional): Format tanggal (default: 'd F Y')

**Returns:** string

**Example:**
```php
echo formatTanggalIndonesia('2024-01-15'); // Output: 15 Januari 2024
echo formatTanggalIndonesia('2024-01-15', 'd/m/Y'); // Output: 15/01/2024
```

### `formatTanggalWaktuIndonesia($tanggal, $format = 'd F Y, H:i')`
Mengubah tanggal dan waktu menjadi format Indonesia.

**Parameters:**
- `$tanggal` (string/DateTime): Tanggal yang akan diformat
- `$format` (string, optional): Format tanggal dan waktu (default: 'd F Y, H:i')

**Returns:** string

**Example:**
```php
echo formatTanggalWaktuIndonesia('2024-01-15 14:30:00'); // Output: 15 Januari 2024, 14:30
```

## Instansi Settings Functions

### `getInstansiSetting($key = null)`
Mendapatkan pengaturan instansi dari database.

**Parameters:**
- `$key` (string, optional): Key pengaturan yang ingin diambil

**Returns:** mixed

**Example:**
```php
// Ambil semua pengaturan
$settings = getInstansiSetting();

// Ambil pengaturan tertentu
$namaInstansi = getInstansiSetting('nama_instansi');
$alamatInstansi = getInstansiSetting('alamat_instansi');
```

### `getInstansiLogoUrl()`
Mendapatkan URL logo instansi yang aman dalam format base64 data URL.

**Returns:** string

**Features:**
- Validasi keberadaan file
- Validasi tipe file (hanya gambar yang diperbolehkan)
- Error handling yang aman
- Return string kosong jika file tidak ada atau error

**Example:**
```php
$logoUrl = getInstansiLogoUrl();
if ($logoUrl) {
    echo '<img src="' . $logoUrl . '" alt="Logo">';
}
```

## Payment Functions

### `canCancelPayment($pembayaran, $userId)`
Mengecek apakah pembayaran dapat dibatalkan.

**Parameters:**
- `$pembayaran` (Pembayaran): Model pembayaran
- `$userId` (int): ID user yang melakukan pengecekan

**Returns:** array

**Return Structure:**
```php
[
    'can_cancel' => bool,
    'message' => string,
    'reason' => string
]
```

**Example:**
```php
$result = canCancelPayment($pembayaran, auth()->id());
if ($result['can_cancel']) {
    // Lakukan pembatalan
} else {
    echo $result['reason'];
}
```

## Utility Functions

### `terbilang($angka)`
Mengubah angka menjadi terbilang dalam bahasa Indonesia.

**Parameters:**
- `$angka` (int/float): Angka yang akan diubah

**Returns:** string

**Example:**
```php
echo terbilang(1234567); // Output: satu juta dua ratus tiga puluh empat ribu lima ratus enam puluh tujuh
```

## Usage in Blade Templates

### Logo Instansi
```php
@php
    $logoUrl = getInstansiLogoUrl();
@endphp
@if($logoUrl)
    <img src="{{ $logoUrl }}" alt="Logo Instansi">
@endif
```

### Format Rupiah
```php
{{ formatRupiah($total) }}
```

### Format Tanggal
```php
{{ formatTanggalIndonesia($tanggal) }}
```

### Instansi Settings
```php
{{ getInstansiSetting('nama_instansi') }}
{{ getInstansiSetting('alamat_instansi') }}
```

## Error Handling

Semua helper functions sudah dilengkapi dengan error handling yang aman:

- **File Operations**: Menggunakan try-catch untuk operasi file
- **Database Operations**: Menggunakan null coalescing operator
- **Validation**: Validasi input dan tipe data
- **Fallback Values**: Memberikan nilai default jika data tidak ada

## Testing

Helper functions dapat ditest menggunakan PHPUnit. Contoh test tersedia di `tests/Unit/HelperTest.php`. 