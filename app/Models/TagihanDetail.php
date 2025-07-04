<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\ModelStatus\HasStatuses;

class TagihanDetail extends Model
{
    use HasFactory, HasStatuses;
    
    protected $table = 'tagihan_details';
    protected $fillable = [
        'tagihan_id', 
        'pembayaran_id',  // Tambahkan pembayaran_id 
        'nama_biaya', 
        'jumlah_biaya', 
        'status'
    ];

    /**
     * Validasi status yang diperbolehkan untuk tagihan detail
     */
    public function isValidStatus(string $name, ?string $reason = null): bool
    {
        $validStatuses = [
            'unpaid',       // Belum dibayar
            'partial',      // Dibayar sebagian
            'paid',         // Lunas
            'overdue'       // Jatuh tempo
        ];

        return in_array($name, $validStatuses);
    }

    /**
     * Get the tagihan that owns this detail
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    /**
     * Get latest payment for this detail
     */
    public function latest_payment(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
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
