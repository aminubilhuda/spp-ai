<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\TagihanDetail;
use App\Models\Pembayaran;
use App\Models\Jurusan;
use App\Models\Biaya;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class KwitansiSerentakTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $operator;
    protected $siswa;
    protected $tagihan;
    protected $tagihanDetails;
    protected $pembayaranIds;

    protected function setUp(): void
    {
        parent::setUp();

        // Create operator user
        $this->operator = User::factory()->create([
            'akses' => 'operator',
            'name' => 'Test Operator',
            'nohp' => '081234567890'
        ]);

        // Create jurusan
        $jurusan = Jurusan::factory()->create([
            'nama' => 'Teknik Komputer dan Jaringan'
        ]);

        // Create siswa
        $this->siswa = Siswa::factory()->create([
            'nama' => 'Ahmad Siswa',
            'nisn' => '12345678',
            'kelas' => 'XI',
            'jurusan_id' => $jurusan->id,
            'angkatan' => '2023'
        ]);

        // Create biaya
        $biaya1 = Biaya::factory()->create([
            'nama' => 'SPP',
            'jumlah' => 500000
        ]);

        $biaya2 = Biaya::factory()->create([
            'nama' => 'Uang Makan',
            'jumlah' => 200000
        ]);

        // Create tagihan
        $this->tagihan = Tagihan::factory()->create([
            'siswa_id' => $this->siswa->id,
            'tanggal_tagihan' => '2024-01-01',
            'tanggal_jatuh_tempo' => '2024-01-31'
        ]);

        // Create tagihan details
        $this->tagihanDetails = [
            TagihanDetail::factory()->create([
                'tagihan_id' => $this->tagihan->id,
                'biaya_id' => $biaya1->id,
                'nama_biaya' => 'SPP',
                'jumlah_biaya' => 500000
            ]),
            TagihanDetail::factory()->create([
                'tagihan_id' => $this->tagihan->id,
                'biaya_id' => $biaya2->id,
                'nama_biaya' => 'Uang Makan',
                'jumlah_biaya' => 200000
            ])
        ];

        // Create pembayaran records
        $this->pembayaranIds = [];
        foreach ($this->tagihanDetails as $detail) {
            $pembayaran = Pembayaran::factory()->create([
                'tagihan_id' => $this->tagihan->id,
                'tagihan_detail_id' => $detail->id,
                'wali_id' => null,
                'tanggal_bayar' => '2024-12-15',
                'jumlah_dibayar' => $detail->jumlah_biaya,
                'metode_pembayaran' => 'Cash',
                'status_konfirmasi' => 'Sudah Dikonfirmasi',
                'user_id' => $this->operator->id
            ]);
            $this->pembayaranIds[] = $pembayaran->id;
        }
    }

    /** @test */
    public function operator_can_view_batch_kwitansi()
    {
        $this->actingAs($this->operator);

        $response = $this->post('/operator/kwitansi/batch', [
            'pembayaran_ids' => $this->pembayaranIds
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('operator.kwitansi_pembayaran_serentak');
        $response->assertViewHas('pembayaran');
        $response->assertViewHas('pembayaranList');
        $response->assertViewHas('pembayaranIds');
    }

    /** @test */
    public function batch_kwitansi_shows_correct_student_info()
    {
        $this->actingAs($this->operator);

        $response = $this->post('/operator/kwitansi/batch', [
            'pembayaran_ids' => $this->pembayaranIds
        ]);

        $response->assertSee('Ahmad Siswa');
        $response->assertSee('12345678');
        $response->assertSee('XI');
    }

    /** @test */
    public function batch_kwitansi_shows_all_payment_items()
    {
        $this->actingAs($this->operator);

        $response = $this->post('/operator/kwitansi/batch', [
            'pembayaran_ids' => $this->pembayaranIds
        ]);

        $response->assertSee('SPP');
        $response->assertSee('Uang Makan');
        $response->assertSee('500.000');
        $response->assertSee('200.000');
    }

    /** @test */
    public function batch_kwitansi_shows_correct_total()
    {
        $this->actingAs($this->operator);

        $response = $this->post('/operator/kwitansi/batch', [
            'pembayaran_ids' => $this->pembayaranIds
        ]);

        $response->assertSee('700.000'); // 500.000 + 200.000
    }

    /** @test */
    public function batch_kwitansi_shows_payment_method()
    {
        $this->actingAs($this->operator);

        $response = $this->post('/operator/kwitansi/batch', [
            'pembayaran_ids' => $this->pembayaranIds
        ]);

        $response->assertSee('Cash');
    }

    /** @test */
    public function batch_kwitansi_shows_transaction_date()
    {
        $this->actingAs($this->operator);

        $response = $this->post('/operator/kwitansi/batch', [
            'pembayaran_ids' => $this->pembayaranIds
        ]);

        $response->assertSee('15-12-2024');
    }

    /** @test */
    public function batch_kwitansi_requires_valid_payment_ids()
    {
        $this->actingAs($this->operator);

        $response = $this->post('/operator/kwitansi/batch', [
            'pembayaran_ids' => [99999] // Invalid ID
        ]);

        $response->assertStatus(302); // Redirect with validation error
    }

    /** @test */
    public function batch_kwitansi_requires_at_least_one_payment()
    {
        $this->actingAs($this->operator);

        $response = $this->post('/operator/kwitansi/batch', [
            'pembayaran_ids' => []
        ]);

        $response->assertStatus(302); // Redirect with validation error
    }

    /** @test */
    public function batch_kwitansi_validates_same_student()
    {
        $this->actingAs($this->operator);

        // Create another student and payment
        $siswa2 = Siswa::factory()->create(['nama' => 'Siswa Lain']);
        $tagihan2 = Tagihan::factory()->create(['siswa_id' => $siswa2->id]);
        $detail2 = TagihanDetail::factory()->create(['tagihan_id' => $tagihan2->id]);
        $pembayaran2 = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan2->id,
            'tagihan_detail_id' => $detail2->id
        ]);

        // Mix payments from different students
        $mixedIds = array_merge($this->pembayaranIds, [$pembayaran2->id]);

        $response = $this->post('/operator/kwitansi/batch', [
            'pembayaran_ids' => $mixedIds
        ]);

        $response->assertStatus(302); // Redirect with error
    }

    /** @test */
    public function batch_kwitansi_can_generate_pdf()
    {
        $this->actingAs($this->operator);

        $response = $this->post('/operator/kwitansi/batch', [
            'pembayaran_ids' => $this->pembayaranIds,
            'format' => 'pdf'
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function non_operator_cannot_access_batch_kwitansi()
    {
        $wali = User::factory()->create([
            'akses' => 'wali',
            'nohp' => '081234567891'
        ]);

        $this->actingAs($wali);

        $response = $this->post('/operator/kwitansi/batch', [
            'pembayaran_ids' => $this->pembayaranIds
        ]);

        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function batch_kwitansi_updates_tagihan_detail_status()
    {
        $this->actingAs($this->operator);

        // Create unconfirmed payment
        $unconfirmedPayment = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'tagihan_detail_id' => $this->tagihanDetails[0]->id,
            'status_konfirmasi' => 'Belum Dikonfirmasi'
        ]);

        $response = $this->post('/operator/kwitansi/batch', [
            'pembayaran_ids' => [$unconfirmedPayment->id]
        ]);

        $response->assertStatus(200);

        // Check that tagihan detail is not updated for unconfirmed payment
        $this->assertDatabaseMissing('tagihan_details', [
            'id' => $this->tagihanDetails[0]->id,
            'pembayaran_id' => $unconfirmedPayment->id
        ]);
    }

    /** @test */
    public function batch_kwitansi_handles_empty_payment_list()
    {
        $this->actingAs($this->operator);

        $response = $this->post('/operator/kwitansi/batch', [
            'pembayaran_ids' => [99999, 99998] // Non-existent IDs
        ]);

        $response->assertStatus(302); // Redirect with error
    }
} 