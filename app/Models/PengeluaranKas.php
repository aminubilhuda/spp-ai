<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengeluaranKas extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran_kas';
    protected $fillable = [
        'tanggal',
        'jumlah',
        'kategori',
        'keterangan',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 