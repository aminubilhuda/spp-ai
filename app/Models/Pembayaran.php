<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasFormatRupiah;
use Spatie\ModelStatus\HasStatuses;

class Pembayaran extends Model
{
    use HasFormatRupiah, HasStatuses;

    protected $table = 'pembayarans';
    protected $fillable = [
        'tagihan_id',
        'tagihan_detail_id',
        'wali_id',
        'status_konfirmasi',
        'jumlah_dibayar',
        'bukti_bayar',
        'metode_pembayaran',
        'user_id',
        'tanggal_bayar',
        'bank_sekolah_id',
        'no_rekening_pengirim',
        'bank_pengirim'
    ];

    protected $dates = ['tanggal_bayar'];

    /**
     * The relationships that should be eager loaded.
     *
     * @var array
     */
    protected $with = ['tagihan.siswa'];

    /**
     * Validasi status yang diperbolehkan untuk pembayaran
     */
    public function isValidStatus(string $name, ?string $reason = null): bool
    {
        $validStatuses = [
            'pending',      // Menunggu konfirmasi
            'confirmed',    // Sudah dikonfirmasi
            'rejected',     // Ditolak
            'cancelled'     // Dibatalkan
        ];

        return in_array($name, $validStatuses);
    }

    /**
     * Get the tagihan that owns the payment
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    /**
     * Get the tagihan detail that owns the payment
     */
    public function tagihan_detail(): BelongsTo
    {
        return $this->belongsTo(TagihanDetail::class);
    }

    /**
     * Get the wali that owns the payment
     */
    public function wali(): BelongsTo
    {
        return $this->belongsTo(Wali::class);
    }

    /**
     * Get the user (admin/operator) that confirmed the payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bank sekolah that owns the payment
     */
    public function bank_sekolah(): BelongsTo
    {
        return $this->belongsTo(BankSekolah::class);
    }

    /**
     * Get formatted status konfirmasi
     */
    public function getStatusKonfirmasiFormattedAttribute()
    {
        return str_replace('_', ' ', $this->status_konfirmasi);
    }

    /**
     * Get formatted payment method
     */
    public function getMetodePembayaranFormattedAttribute()
    {
        return str_replace('_', ' ', $this->metode_pembayaran);
    }
}
