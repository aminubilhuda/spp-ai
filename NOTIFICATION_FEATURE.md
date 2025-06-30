# Fitur Notifikasi Pembayaran untuk Operator

## Deskripsi
Fitur ini menambahkan sistem notifikasi real-time untuk operator ketika wali murid melakukan pembayaran tagihan. Operator akan menerima notifikasi yang dapat dilihat di navbar dan halaman khusus notifikasi.

## Komponen yang Ditambahkan

### 1. Notification Class
- **File**: `app/Notifications/PembayaranNotification.php`
- **Fungsi**: Menangani format dan konten notifikasi
- **Channel**: Database (disimpan di tabel notifications)
- **Data**: Informasi pembayaran, wali murid, siswa, dan jumlah pembayaran

### 2. Observer
- **File**: `app/Observers/PembayaranObserver.php`
- **Fungsi**: Mengirim notifikasi otomatis ketika pembayaran baru dibuat oleh wali
- **Kondisi**: Hanya mengirim notifikasi jika pembayaran dibuat oleh user dengan akses 'wali'

### 3. Controller
- **File**: `app/Http/Controllers/NotificationController.php`
- **Method**:
  - `markAsRead($id)` - Menandai notifikasi sebagai dibaca
  - `markAllAsRead()` - Menandai semua notifikasi sebagai dibaca
  - `unreadCount()` - Mendapatkan jumlah notifikasi belum dibaca
  - `index()` - Menampilkan halaman notifikasi

### 4. Routes
- **File**: `routes/web.php`
- **Routes**:
  - `GET /operator/notifications` - Halaman notifikasi
  - `POST /operator/notifications/{id}/mark-as-read` - Tandai sebagai dibaca
  - `POST /operator/notifications/mark-all-as-read` - Tandai semua sebagai dibaca
  - `GET /operator/notifications/unread-count` - Jumlah belum dibaca

### 5. Views
- **File**: `resources/views/operator/notifications_index.blade.php`
- **Fungsi**: Halaman untuk melihat semua notifikasi dengan fitur mark as read

### 6. UI Components
- **Navbar**: Dropdown notifikasi dengan badge counter
- **Sidebar**: Menu notifikasi dengan badge counter
- **CSS**: Styling untuk notifikasi (`public/css/notifications.css`)

## Cara Kerja

### 1. Trigger Notifikasi
```php
// Ketika wali membuat pembayaran baru
$pembayaran = Pembayaran::create([
    'tagihan_id' => $request->tagihan_id,
    'wali_id' => $tagihan->siswa->wali_id,
    // ... data lainnya
    'user_id' => auth()->id(), // ID wali yang membuat pembayaran
]);
```

### 2. Observer Menangkap Event
```php
public function created(Pembayaran $pembayaran): void
{
    // Load user relationship
    $pembayaran->load('user');
    
    // Kirim notifikasi ke operator jika pembayaran dibuat oleh wali
    if ($pembayaran->user && $pembayaran->user->akses === 'wali') {
        $this->sendNotificationToOperators($pembayaran);
    }
}
```

### 3. Pengiriman Notifikasi
```php
private function sendNotificationToOperators(Pembayaran $pembayaran): void
{
    // Load relationships
    $pembayaran->load(['tagihan.siswa.wali', 'tagihan_detail', 'user']);
    
    // Ambil semua operator
    $operators = User::where('akses', 'operator')->get();
    
    // Kirim notifikasi
    Notification::send($operators, new PembayaranNotification($pembayaran));
}
```

## Fitur UI

### 1. Navbar Notifikasi
- Icon bell dengan badge counter
- Dropdown dengan daftar 5 notifikasi terbaru
- Tombol "Mark all as read"
- Link ke halaman notifikasi lengkap

### 2. Sidebar Menu
- Menu "Notifikasi" dengan badge counter
- Menampilkan jumlah notifikasi belum dibaca

### 3. Halaman Notifikasi
- Tabel semua notifikasi dengan pagination
- Filter berdasarkan status (dibaca/belum dibaca)
- Tombol "Tandai Semua Dibaca"
- Tombol aksi untuk setiap notifikasi

## JavaScript Features

### 1. Real-time Updates
- Auto refresh badge counter setiap 30 detik
- AJAX untuk mark as read tanpa reload halaman

### 2. Interactive Elements
- Click to mark as read
- Mark all as read functionality
- Smooth animations dan transitions

## Database Schema

### Tabel notifications
```sql
CREATE TABLE notifications (
    id UUID PRIMARY KEY,
    type VARCHAR NOT NULL,
    notifiable_type VARCHAR NOT NULL,
    notifiable_id BIGINT NOT NULL,
    data TEXT NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## Data Notifikasi
```json
{
    "tagihan_id": 1,
    "wali_id": 2,
    "pembayaran_id": 5,
    "title": "Pembayaran Tagihan Baru",
    "message": "Wali murid John Doe telah melakukan pembayaran tagihan sebesar 500.000 untuk siswa Jane Doe",
    "siswa_nama": "Jane Doe",
    "jumlah_dibayar": 500000,
    "metode_pembayaran": "Bank Transfer",
    "tanggal_bayar": "2024-01-15"
}
```

## Testing

### 1. Test Pembayaran Wali
1. Login sebagai wali murid
2. Buat pembayaran baru
3. Login sebagai operator
4. Cek notifikasi di navbar dan halaman notifikasi

### 2. Test Mark as Read
1. Klik notifikasi di dropdown
2. Cek badge counter berkurang
3. Cek status notifikasi berubah

### 3. Test Mark All as Read
1. Klik tombol "Mark all as read"
2. Cek semua notifikasi berubah status
3. Cek badge counter hilang

## Troubleshooting

### 1. Notifikasi Tidak Muncul
- Cek observer terdaftar di `AppServiceProvider`
- Cek relasi model Pembayaran dengan User
- Cek log error di `storage/logs/laravel.log`

### 2. Badge Counter Tidak Update
- Cek JavaScript console untuk error
- Cek route `/operator/notifications/unread-count` berfungsi
- Cek CSRF token valid

### 3. Relasi Error
- Pastikan model relationships didefinisikan dengan benar
- Gunakan eager loading untuk menghindari N+1 queries
- Cek foreign key constraints

## Future Enhancements

1. **Email Notifications**: Tambahkan channel email untuk notifikasi penting
2. **Push Notifications**: Implementasi push notification untuk mobile
3. **Notification Preferences**: Operator dapat mengatur preferensi notifikasi
4. **Notification Templates**: Template yang dapat dikustomisasi
5. **Notification History**: Riwayat lengkap notifikasi dengan filter
6. **Real-time Updates**: WebSocket untuk update real-time tanpa refresh 