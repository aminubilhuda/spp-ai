<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'instansi_settings';
    
    protected $fillable = [
        'name', 'val', 
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