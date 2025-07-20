<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstansiSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_instansi',
        'email_instansi',
        'nomor_wa_instansi',
        'alamat_instansi',
        'logo_instansi',
        'nama_penanggung_jawab',
        'nama_jabatan',
        'ttd_penanggung_jawab',
    ];

    /**
     * Mendapatkan pengaturan instansi (hanya mengambil record pertama)
     */
    public static function getInstansiSettings()
    {
        return static::first() ?? new static();
    }

    /**
     * Menyimpan atau memperbarui pengaturan instansi
     */
    public static function saveInstansiSettings($data)
    {
        $setting = static::first();
        
        if (!$setting) {
            $setting = new static();
        }
        
        $setting->fill($data);
        $setting->save();
        
        return $setting;
    }
} 