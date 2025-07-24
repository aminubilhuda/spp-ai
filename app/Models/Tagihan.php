<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFormatRupiah;
use Spatie\ModelStatus\HasStatuses;
use App\Traits\Syncable;

class Tagihan extends Model
{
    use HasFactory;
    use HasFormatRupiah;
    use HasStatuses, Syncable;

    protected $fillable = [
        'tahun_pelajaran_id', 'siswa_id', 'user_id', 'angkatan', 'jurusan', 'kelas', 'tanggal_tagihan', 'tanggal_jatuh_tempo', 'keterangan', 'denda', 'synced',
    ];
    protected $dates = [
        'tanggal_tagihan',
        'tanggal_jatuh_tempo',
        'created_at',
        'updated_at'
    ];

    public function getJumlahTagihanAttribute()
    {
        return $this->tagihan_details->sum('jumlah_biaya');
    }

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

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
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