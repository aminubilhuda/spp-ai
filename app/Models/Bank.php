<?php

/*
 * This file is part of the IndoBank package.
 *
 * (c) Andri Desmana <andridesmana.pw | andridesmana29@gmail.com>
 *
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Bank Model.
 */
class Bank extends Model
{
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
        'nama_bank'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}