<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\Siswa;
use App\Models\TagihanDetail;
use App\Models\Biaya;
use App\Models\Jurusan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class NotificationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create operator user
        $this->operator = User::factory()->create([
            'akses' => 'operator',
            'email' => 'operator@test.com',
            'nohp' => '081234567890',
            'password' => bcrypt('password')
        ]);

        // Create wali user
        $this->wali = User::factory()->create([
            'akses' => 'wali',
            'email' => 'wali@test.com',
            'nohp' => '089876543210',
            'password' => bcrypt('password')
        ]);

        // Create jurusan
        $this->jurusan = Jurusan::create([
            'nama' => 'Teknik Komputer dan Jaringan',
            'keterangan' => 'Jurusan TKJ'
        ]);

        // Create siswa
        $this->siswa = Siswa::create([
            'wali_id' => $this->wali->id,
            'nama' => 'John Doe',
            'nisn' => '1234567890',
            'nis' => '12345',
            'jurusan_id' => $this->jurusan->id,
            'kelas' => 'XII',
            'angkatan' => '2022',
            'jenis_kelamin' => 'L'
        ]);

        // Create biaya
        $this->biaya = Biaya::create([
            'nama' => 'SPP Bulanan',
            'jumlah' => 500000,
            'user_id' => $this->operator->id
        ]);

        // Create tagihan
        $this->tagihan = Tagihan::create([
            'siswa_id' => $this->siswa->id,
            'total_nilai' => 500000
        ]);

        // Create tagihan detail
        $this->tagihanDetail = TagihanDetail::create([
            'tagihan_id' => $this->tagihan->id,
            'biaya_id' => $this->biaya->id,
            'nama_biaya' => $this->biaya->nama,
            'jumlah_biaya' => 500000,
            'status' => 'belum_lunas'
        ]);
    }

    /** @test */
    public function operator_can_see_notifications_page()
    {
        $response = $this->actingAs($this->operator)
            ->get('/operator/notifications');

        $response->assertStatus(200);
        $response->assertViewIs('operator.notifications_index');
    }

    /** @test */
    public function wali_cannot_access_notifications_page()
    {
        $response = $this->actingAs($this->wali)
            ->get('/operator/notifications');

        $response->assertStatus(403);
    }

    /** @test */
    public function notification_is_created_when_wali_makes_payment()
    {
        // Create payment as wali
        $pembayaran = Pembayaran::create([
            'tagihan_id' => $this->tagihan->id,
            'tagihan_detail_id' => $this->tagihanDetail->id,
            'wali_id' => $this->wali->id,
            'tanggal_bayar' => now(),
            'jumlah_dibayar' => 500000,
            'metode_pembayaran' => 'Bank Transfer',
            'status_konfirmasi' => 'Belum Dikonfirmasi',
            'user_id' => $this->wali->id,
            'bukti_bayar' => 'test.jpg'
        ]);

        // Check if notification was created for operator
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $this->operator->id,
            'type' => 'App\Notifications\PembayaranNotification'
        ]);

        // Check notification data
        $notification = $this->operator->notifications()->first();
        $this->assertEquals('Pembayaran Tagihan Baru', $notification->data['title']);
        $this->assertEquals(500000, $notification->data['jumlah_dibayar']);
    }

    /** @test */
    public function notification_is_not_created_when_operator_makes_payment()
    {
        // Create payment as operator
        $pembayaran = Pembayaran::create([
            'tagihan_id' => $this->tagihan->id,
            'tagihan_detail_id' => $this->tagihanDetail->id,
            'wali_id' => $this->wali->id,
            'tanggal_bayar' => now(),
            'jumlah_dibayar' => 500000,
            'metode_pembayaran' => 'Cash',
            'status_konfirmasi' => 'Sudah Dikonfirmasi',
            'user_id' => $this->operator->id
        ]);

        // Check that no notification was created
        $this->assertDatabaseMissing('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $this->operator->id,
            'type' => 'App\Notifications\PembayaranNotification'
        ]);
    }

    /** @test */
    public function operator_can_mark_notification_as_read()
    {
        // Create a notification
        $this->operator->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\PembayaranNotification',
            'data' => [
                'title' => 'Test Notification',
                'message' => 'Test message'
            ]
        ]);

        $notification = $this->operator->notifications()->first();

        $response = $this->actingAs($this->operator)
            ->post("/operator/notifications/{$notification->id}/mark-as-read");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Check if notification is marked as read
        $this->assertNotNull($notification->fresh()->read_at);
    }

    /** @test */
    public function operator_can_mark_all_notifications_as_read()
    {
        // Create multiple notifications
        for ($i = 0; $i < 3; $i++) {
            $this->operator->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\Notifications\PembayaranNotification',
                'data' => [
                    'title' => "Test Notification {$i}",
                    'message' => "Test message {$i}"
                ]
            ]);
        }

        $response = $this->actingAs($this->operator)
            ->post('/operator/notifications/mark-all-as-read');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Check if all notifications are marked as read
        $this->assertEquals(0, $this->operator->unreadNotifications()->count());
    }

    /** @test */
    public function operator_can_get_unread_notification_count()
    {
        // Create unread notifications
        for ($i = 0; $i < 5; $i++) {
            $this->operator->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\Notifications\PembayaranNotification',
                'data' => [
                    'title' => "Test Notification {$i}",
                    'message' => "Test message {$i}"
                ]
            ]);
        }

        $response = $this->actingAs($this->operator)
            ->get('/operator/notifications/unread-count');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 5
        ]);
    }

    /** @test */
    public function notification_contains_correct_data()
    {
        // Create payment as wali
        $pembayaran = Pembayaran::create([
            'tagihan_id' => $this->tagihan->id,
            'tagihan_detail_id' => $this->tagihanDetail->id,
            'wali_id' => $this->wali->id,
            'tanggal_bayar' => now(),
            'jumlah_dibayar' => 750000,
            'metode_pembayaran' => 'Bank Transfer',
            'status_konfirmasi' => 'Belum Dikonfirmasi',
            'user_id' => $this->wali->id,
            'bukti_bayar' => 'test.jpg'
        ]);

        $notification = $this->operator->notifications()->first();
        $data = $notification->data;

        $this->assertEquals($this->tagihan->id, $data['tagihan_id']);
        $this->assertEquals($this->wali->id, $data['wali_id']);
        $this->assertEquals($pembayaran->id, $data['pembayaran_id']);
        $this->assertEquals('Pembayaran Tagihan Baru', $data['title']);
        $this->assertEquals(750000, $data['jumlah_dibayar']);
        $this->assertEquals('Bank Transfer', $data['metode_pembayaran']);
        $this->assertEquals($this->siswa->nama, $data['siswa_nama']);
    }
} 