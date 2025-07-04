<?php

namespace App\Models;

use Spatie\ModelStatus\Status as SpatieStatus;

class Status extends SpatieStatus
{
    protected $fillable = [
        'name',
        'reason',
        'model_type',
        'model_id'
    ];
}
