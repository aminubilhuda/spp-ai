<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TagihanDetail extends Model
{
    use HasFactory;
    
    protected $table = 'tagihan_details';
    protected $fillable = ['tagihan_id', 'nama_biaya', 'jumlah_biaya', 'status'];

    /**
     * Get the tagihan that owns this detail
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    /**
     * Get all pembayaran records for this detail
     */
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_detail_id');
    }

    public function getStatusDetailAttribute()
    {
        return match($this->status) {
            'baru' => 'Baru',
            'angsur' => 'Diangsur',
            'lunas' => 'Lunas',
            'belum_lunas' => 'Belum Lunas',
            default => ucfirst(str_replace('_', ' ', $this->status))
        };
    }
}
