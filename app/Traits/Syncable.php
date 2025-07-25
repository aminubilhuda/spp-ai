<?php

namespace App\Traits;

use App\Jobs\SyncToOnlineJob;

// App\Traits\Syncable.php
trait Syncable
{
    protected static function bootSyncable()
    {
        // static::created(function ($model) {
        //     if (!$model->synced) {
        //         SyncToOnlineJob::dispatch('created', $model->toArray());
        //     }
        // });

        // static::updated(function ($model) {
        //     if (!$model->synced) {
        //         SyncToOnlineJob::dispatch('updated', $model->toArray());
        //     }
        // });

        // static::deleted(function ($model) {
        //     if (!$model->synced) {
        //         SyncToOnlineJob::dispatch('deleted', ['id' => $model->id]);
        //     }
        // });
    }
}