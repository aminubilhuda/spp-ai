<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\ModelStatus\HasStatuses;

class Siswa extends Model
{
    /** @use HasFactory<\Database\Factories\SiswaFactory> */
    use HasFactory;
    use HasStatuses;
    protected $fillable = [
        'wali_id', 'wali_status', 'nama', 'nisn', 'nis', 'foto', 'jenis_kelamin', 'jurusan_id', 'kelas', 'angkatan', 'user_id', // tambahkan field lain jika ada
        'sync_id', 'synced_at', 'sync_status', 'source_system',
    ];

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

    // method untuk mendapatkan biaya SPP berdasarkan tagihan
    public function getBiayaSppAttribute()
    {
        // Ambil tagihan terbaru untuk siswa ini
        $tagihan = $this->tagihan()
                        ->with('tagihan_details')
                        ->latest()
                        ->first();
        
        if ($tagihan && $tagihan->tagihan_details->count() > 0) {
            return $tagihan->tagihan_details->sum('jumlah_biaya');
        }
        
        return 0;
    }

    // method untuk mendapatkan total tagihan
    public function getTotalTagihanAttribute()
    {
        return $this->tagihan()
                    ->with('tagihan_details')
                    ->get()
                    ->sum(function($tagihan) {
                        return $tagihan->tagihan_details->sum('jumlah_biaya');
                    });
    }

    // method untuk mendapatkan total pembayaran
    public function getTotalPembayaranAttribute()
    {
        return $this->tagihan()
                    ->with(['tagihan_details.pembayaran'])
                    ->get()
                    ->sum(function($tagihan) {
                        return $tagihan->tagihan_details->sum(function($detail) {
                            return $detail->pembayaran
                                         ->where('status_konfirmasi', 'Sudah Dikonfirmasi')
                                         ->sum('jumlah_dibayar');
                        });
                    });
    }
}