<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFormatRupiah;

class Tagihan extends Model
{
    use HasFactory;
    use HasFormatRupiah;

    protected $guarded = [];
    protected $dates = ['tanggal_tagihan', 'tanggal_jatuh_tempo'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function biaya()
    {
        return $this->belongsTo(Biaya::class);
    }

    public function tagihan_details()
    {
        return $this->hasMany(TagihanDetail::class);
    }

    // Format status for display
    public function getStatusTagihanAttribute()
    {
        return match($this->status) {
            'baru' => 'Baru',
            'angsur' => 'Diangsur',
            'lunas' => 'Lunas',
            'belum_lunas' => 'Belum Lunas',
            default => '',
        };
    }
}