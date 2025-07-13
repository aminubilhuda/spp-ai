<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunPelajaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama', 'is_aktif',
        'sync_id', 'synced_at', 'sync_status', 'source_system',
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