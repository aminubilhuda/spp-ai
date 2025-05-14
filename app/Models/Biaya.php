<?php

namespace App\Models;

use App\Traits\HasFormatRupiah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Biaya extends Model
{
    /** @use HasFactory<\Database\Factories\BiayaFactory> */
    use HasFactory;
    use HasFormatRupiah;
    
    protected $fillable = ['nama', 'jumlah', 'user_id'];

    /**
     * Get the jumlah attribute without decimal places
     */
    public function getJumlahAttribute($value)
    {
        return $value ? (int)$value : 0;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($biaya) {
            $biaya->user_id = auth()->id();
        });
        
        static::updating(function ($biaya) {
            $biaya->user_id = auth()->id();
        });
    }
}