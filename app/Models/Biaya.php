<?php

namespace App\Models;

use App\Traits\HasFormatRupiah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Biaya extends Model
{
    /** @use HasFactory<\Database\Factories\BiayaFactory> */
    use HasFactory;
    use HasFormatRupiah;
    
    protected $fillable = ['parent_id', 'nama', 'jumlah', 'user_id'];
    protected $appends = ['total_tagihan'];

    
    /**
     * Get the jumlah attribute without decimal places
     */
    
    public function getTotalTagihanAttribute()
    {
        // Jika ini adalah parent, hitung total dari semua children + jumlah parent
        if (is_null($this->parent_id)) {
            return $this->jumlah + $this->children->sum('jumlah');
        }
        
        // Jika ini adalah child, return jumlah child saja
        return $this->jumlah;
    }

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

    public function children(): HasMany
    {
        return $this->hasMany(Biaya::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Biaya::class, 'parent_id');
    }

    /**
     * Check if this is a parent biaya
     */
    public function isParent()
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this is a child biaya
     */
    public function isChild()
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get all child biayas as array for tagihan detail
     */
    public function getChildBiayasForTagihan()
    {
        if ($this->isParent()) {
            return $this->children->map(function($child) {
                return [
                    'nama_biaya' => $child->nama,
                    'jumlah_biaya' => $child->jumlah,
                    'biaya_id' => $child->id
                ];
            })->toArray();
        }
        
        return [[
            'nama_biaya' => $this->nama,
            'jumlah_biaya' => $this->jumlah,
            'biaya_id' => $this->id
        ]];
    }
}