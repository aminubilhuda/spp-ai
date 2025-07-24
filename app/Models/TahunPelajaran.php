<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Syncable;

class TahunPelajaran extends Model
{
    use HasFactory, Syncable;

    protected $fillable = [
        'nama', 'is_aktif', 'synced',
    ];

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class, 'tahun_pelajaran_id');
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'tahun_pelajaran_id');
    }
} 