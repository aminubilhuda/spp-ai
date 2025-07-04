<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pembayaran;
use App\Models\TagihanDetail;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contoh penggunaan status untuk pembayaran
        // $pembayaran = Pembayaran::first();
        // if ($pembayaran) {
        //     $pembayaran->setStatus('pending', 'Menunggu konfirmasi dari operator');
        // }

        // Contoh penggunaan status untuk tagihan detail
        // $tagihanDetail = TagihanDetail::first();
        // if ($tagihanDetail) {
        //     $tagihanDetail->setStatus('unpaid', 'Belum ada pembayaran');
        // }

        $this->command->info('Status seeder completed. Use setStatus() method to add statuses to models.');
    }
}
