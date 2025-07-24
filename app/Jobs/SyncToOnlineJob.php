<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncToOnlineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $model;

    public function __construct($model, $data)
    {
        $this->model = $model;
        $this->data = $data;
    }

    public function handle()
    {
        try {
            // Tambahkan identifier untuk menandai ini dari local
            $this->data['source'] = 'local';
            
            // Kirim ke server online
            $response = Http::withHeaders([
                'X-API-Key' => config('services.sync.key')
            ])->post(config('services.sync.url') . '/api/sync', $this->data);

            if (!$response->successful()) {
                // Jika gagal, throw exception agar job bisa di-retry
                throw new \Exception('Sync failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            // Log error dan retry jika masih dalam batas retry
            logger()->error($e->getMessage());
            if ($this->attempts() < 3) {
                $this->release(300); // retry after 5 minutes
            }
        }
    }
}