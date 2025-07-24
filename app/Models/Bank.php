<?php

/*
 * This file is part of the IndoBank package.
 *
 * (c) Andri Desmana <andridesmana.pw | andridesmana29@gmail.com>
 *
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Syncable;

/**
 * Bank Model.
 */
class Bank extends Model
{
    use Syncable;
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'banks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sandi_bank',
        'nama_bank',
        'synced',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the bank sekolah records for this bank.
     */
    public function bankSekolahs(): HasMany
    {
        return $this->hasMany(BankSekolah::class, 'kode_bank', 'sandi_bank');
    }
}