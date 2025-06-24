<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankSekolah extends Model
{
    /** @use HasFactory<\Database\Factories\BankSekolahFactory> */
    use HasFactory;
    
    protected $fillable = [
        'kode_bank',
        'nama_bank', 
        'no_rekening',
        'atas_nama',
        'keterangan'
    ];
}