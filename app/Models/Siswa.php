<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\ModelStatus\HasStatuses;
use App\Traits\Syncable;

class Siswa extends Model
{
    /** @use HasFactory<\Database\Factories\SiswaFactory> */
    use HasFactory;
    use HasStatuses, Syncable;

    // definisikan status
    public const STATUS_AKTIF = 'Aktif';
    public const STATUS_NONAKTIF = 'Nonaktif';
    public const STATUS_LULUS = 'Lulus';

    protected $fillable = [
        'wali_id', 'wali_status', 'nama', 'nisn', 'nis', 'foto', 'jenis_kelamin', 'jurusan_id', 'kelas', 'angkatan', 'user_id', 'synced',
    ];

    /**
     * Get all possible statuses.
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_AKTIF,
            self::STATUS_NONAKTIF,
            self::STATUS_LULUS,
        ];
    }

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