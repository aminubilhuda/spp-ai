<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TagihanDetail;
use App\Models\Siswa;
use App\Models\PengeluaranKas;
use App\Models\User;
use App\Models\Setting;
use App\Models\TahunPelajaran;
use App\Models\Jurusan;
use App\Models\Biaya;
use App\Models\BankSekolah;
use App\Models\Bank;

class SyncController extends Controller
{
    protected $syncedModels = [
        Pembayaran::class,
        Tagihan::class,
        TagihanDetail::class,
        Siswa::class,
        PengeluaranKas::class,
        User::class,
        Setting::class,
        TahunPelajaran::class,
        Jurusan::class,
        Biaya::class,
        BankSekolah::class,
        Bank::class
    ];
    public function sync(Request $request)
    {
        // Validate request
        $data = $request->validate([
            'model' => 'required|string',
            'action' => 'required|in:created,updated,deleted',
            'data' => 'required|array',
            'source' => 'required|string'
        ]);

        try {
            DB::transaction(function () use ($data) {
                $model = $data['model'];
                $action = $data['action'];
                $modelData = $data['data'];

                switch ($action) {
                    case 'created':
                    case 'updated':
                        $model::updateOrCreate(
                            ['id' => $modelData['id']], 
                            array_merge($modelData, ['synced' => true])
                        );
                        break;
                    case 'deleted':
                        $model::find($modelData['id'])?->delete();
                        break;
                }
            });

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getUpdates(Request $request)
    {
        $lastSync = $request->get('last_sync', 0);
        
        // Collect updates from all synced models
        $updates = collect();
        
        foreach ($this->syncedModels as $model) {
            $updates = $updates->merge(
                $model::where('updated_at', '>', Carbon::createFromTimestamp($lastSync))
                    ->get()
                    ->map(function ($item) {
                        return [
                            'model' => get_class($item),
                            'action' => 'updated',
                            'data' => $item->toArray(),
                            'source' => 'online'
                        ];
                    })
            );
        }

        return response()->json($updates);
    }
}