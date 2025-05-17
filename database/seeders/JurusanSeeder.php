<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use Illuminate\Database\Seeder;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jurusans = [
            ['nama' => 'AKUNTANSI', 'keterangan' => 'Program Keahlian Akuntansi'],
            ['nama' => 'BISNIS DIGITAL', 'keterangan' => 'Program Keahlian Bisnis Digital'],
            ['nama' => 'REKAYASA PERANGKAT LUNAK', 'keterangan' => 'Program Keahlian Rekayasa Perangkat Lunak'],
            ['nama' => 'PERHOTELAN', 'keterangan' => 'Program Keahlian Perhotelan'],
            ['nama' => 'FASHION KECANTIKAN', 'keterangan' => 'Program Keahlian Fashion dan Kecantikan'],
        ];

        foreach ($jurusans as $jurusan) {
            Jurusan::create($jurusan);
        }
    }
}