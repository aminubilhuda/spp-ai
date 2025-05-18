<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Siswa extends Model
{
    /** @use HasFactory<\Database\Factories\SiswaFactory> */
    use HasFactory;
    protected $guarded = [];

    // relasi 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // relasi 
    public function wali(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_id')->withDefault([
            'name' => 'Belum ada wali murid',
        ]);
    }
    
    // relasi ke jurusan
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class)->withDefault([
            'nama' => 'Belum ada jurusan',
        ]);
    }

    // relasi ke tagihan
    public function tagihan()
    {
        return $this->hasMany(Tagihan::class);
    }
}