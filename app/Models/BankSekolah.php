<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Syncable;

class BankSekolah extends Model
{
    /** @use HasFactory<\Database\Factories\BankSekolahFactory> */
    use HasFactory, Syncable;
    
    protected $fillable = [
        'kode_bank',
        'nama_bank', 
        'no_rekening',
        'atas_nama',
        'keterangan',
        'synced',
    ];

    /**
     * Get the bank that owns this bank sekolah.
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'kode_bank', 'sandi_bank');
    }
}