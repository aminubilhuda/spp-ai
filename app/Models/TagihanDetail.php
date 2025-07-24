<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\ModelStatus\HasStatuses;
use App\Traits\Syncable;

class TagihanDetail extends Model
{
    use HasFactory, HasStatuses, Syncable;
    
    protected $table = 'tagihan_details';
    protected $fillable = [
        'tagihan_id', 
        'biaya_id',
        'pembayaran_id',  // Tambahkan pembayaran_id 
        'nama_biaya', 
        'jumlah_biaya', 
        'status',
        'tanggal_lunas',
        'synced',
    ];

    protected $dates = [
        'tanggal_lunas',
        'created_at',
        'updated_at'
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

    public function biaya()
    {
        return $this->belongsTo(Biaya::class);
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
        // Hitung total pembayaran yang sudah dikonfirmasi
        $total_pembayaran = $this->pembayaran()
            ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
            ->sum('jumlah_dibayar');

        // Bandingkan dengan jumlah tagihan
        if ($total_pembayaran >= $this->jumlah_biaya) {
            return 'Lunas';
        } elseif ($total_pembayaran > 0) {
            return 'Angsur';
        } else {
            return 'Belum Di Bayar';
        }
    }

    /**
     * Get formatted tanggal lunas
     */
    public function getTanggalLunasFormattedAttribute()
    {
        return $this->tanggal_lunas ? $this->tanggal_lunas->format('d-m-Y H:i') : '-';
    }

    /**
     * Check if tagihan detail is fully paid
     */
    public function isLunas()
    {
        $totalPembayaran = $this->pembayaran()
            ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
            ->sum('jumlah_dibayar');
        
        return $totalPembayaran >= $this->jumlah_biaya;
    }
}