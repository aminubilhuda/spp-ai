<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Syncable;

class Jurusan extends Model
{
    use HasFactory, Syncable;
    
    protected $fillable = [
        'nama', 'keterangan',
        'synced',
    ];
    
    /**
     * Relasi dengan siswa
     */
    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }
}