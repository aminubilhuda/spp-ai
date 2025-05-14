<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TagihanDetail extends Model
{
    use HasFactory;
    
    protected $table = 'tagihan_details';
    protected $fillable = ['tagihan_id', 'nama_biaya', 'jumlah_biaya'];

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }
}
