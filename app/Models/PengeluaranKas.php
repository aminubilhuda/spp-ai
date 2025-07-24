<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Syncable;

class PengeluaranKas extends Model
{
    use HasFactory, Syncable;

    protected $table = 'pengeluaran_kas';
    protected $fillable = [
        'tanggal',
        'jumlah',
        'kategori',
        'keterangan',
        'user_id',
        'synced',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 