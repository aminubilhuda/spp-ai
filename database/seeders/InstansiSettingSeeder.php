<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstansiSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek apakah sudah ada data pengaturan
        if (Setting::count() === 0) {
            Setting::create([
                'nama_instansi' => 'Sekolah Menengah Atas Negeri 1',
                'email_instansi' => 'info@sman1.sch.id',
                'nomor_wa_instansi' => '081234567890',
                'alamat_instansi' => 'Jl. Pendidikan No. 123, Kota Pendidikan, Provinsi Pendidikan'
            ]);
        }
    }
}
