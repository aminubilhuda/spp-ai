<?php

namespace Database\Seeders;

use App\Models\BankSekolah;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            [
                'kode_bank' => '014',
                'nama_bank' => 'Bank BCA',
                'no_rekening' => '1234567890',
                'atas_nama' => 'SMA Negeri 1 Contoh',
                'keterangan' => 'Rekening utama sekolah',
            ],
            [
                'kode_bank' => '008',
                'nama_bank' => 'Bank Mandiri',
                'no_rekening' => '0987654321',
                'atas_nama' => 'SMA Negeri 1 Contoh',
                'keterangan' => 'Rekening cadangan',
            ],
            [
                'kode_bank' => '009',
                'nama_bank' => 'Bank BNI',
                'no_rekening' => '1122334455',
                'atas_nama' => 'SMA Negeri 1 Contoh',
                'keterangan' => 'Rekening khusus SPP',
            ],
        ];

        foreach ($banks as $bank) {
            BankSekolah::create($bank);
        }
    }
}
