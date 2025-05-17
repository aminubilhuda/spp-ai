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
        'wali_id',
        'status_konfirmasi',
        'jumlah_dibayar',
        'bukti_bayar',
        'metode_pembayaran',
        'user_id'
    ];

    /**
     * Get the tagihan that owns the payment
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    /**
     * Get the wali that owns the payment
     */
    public function wali(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_id');
    }

    /**
     * Get the user (admin/operator) that confirmed the payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
