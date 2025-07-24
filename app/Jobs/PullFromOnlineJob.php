<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PullFromOnlineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        try {
            // Ambil data terbaru dari server online
            $response = Http::withHeaders([
                'X-API-Key' => config('services.sync.key')
            ])->get(config('services.sync.url') . '/api/get-updates', [
                'last_sync' => Cache::get('last_online_sync', 0)
            ]);

            if ($response->successful()) {
                $updates = $response->json();
                
                foreach ($updates as $update) {
                    // Skip jika data berasal dari local
                    if ($update['source'] === 'local') {
                        continue;
                    }

                    // Proses update ke database local
                    DB::transaction(function () use ($update) {
                        $model = $update['model'];
                        $action = $update['action'];
                        $data = $update['data'];

                        switch ($action) {
                            case 'created':
                            case 'updated':
                                $model::updateOrCreate(
                                    ['id' => $data['id']], 
                                    array_merge($data, ['synced' => true])
                                );
                                break;
                            case 'deleted':
                                $model::find($data['id'])?->delete();
                                break;
                        }
                    });
                }

                Cache::put('last_online_sync', now()->timestamp);
            }
        } catch (\Exception $e) {
            logger()->error('Pull sync failed: ' . $e->getMessage());
            if ($this->attempts() < 3) {
                $this->release(300);
            }
        }
    }
}