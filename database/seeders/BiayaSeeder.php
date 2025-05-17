<?php

namespace Database\Seeders;

use App\Models\Biaya;
use Illuminate\Database\Seeder;

class BiayaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $biayas = [
            [
                'nama' => 'SPP Bulanan',
                'jumlah' => 350000,
                'user_id' => 1
            ],
            [
                'nama' => 'Uang Gedung',
                'jumlah' => 2500000,
                'user_id' => 1
            ],
            [
                'nama' => 'Seragam Lengkap',
                'jumlah' => 750000,
                'user_id' => 1
            ],
            [
                'nama' => 'Praktikum',
                'jumlah' => 500000,
                'user_id' => 1
            ],
            [
                'nama' => 'Kegiatan Tahunan',
                'jumlah' => 250000,
                'user_id' => 1
            ],
        ];        foreach ($biayas as $biaya) {
            Biaya::create($biaya);
        }
    }
}
