<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasFormatRupiah;

class Pembayaran extends Model
{
    use HasFormatRupiah;

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
        'tanggal_bayar'
    ];

    protected $dates = ['tanggal_bayar'];

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
