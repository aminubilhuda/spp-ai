<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Jurusan;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID jurusan yang sudah ada
        $jurusanRpl = Jurusan::where('nama', 'LIKE', '%REKAYASA PERANGKAT LUNAK%')->first()->id;
        $jurusanAk = Jurusan::where('nama', 'LIKE', '%AKUNTANSI%')->first()->id;
        $jurusanBd = Jurusan::where('nama', 'LIKE', '%BISNIS DIGITAL%')->first()->id;

        // Data siswa
        $siswas = [
            [
                'nama' => 'Budi Santoso',
                'nisn' => '1234567891',
                'nis' => '2025001',
                'jenis_kelamin' => 'Laki-laki',
                'jurusan_id' => $jurusanRpl,
                'kelas' => 'X',
                'angkatan' => '2025',
                'user_id' => 1
            ],
            [
                'nama' => 'Ani Wulandari',
                'nisn' => '1234567892',
                'nis' => '2025002',
                'jenis_kelamin' => 'Perempuan',
                'jurusan_id' => $jurusanAk,
                'kelas' => 'X',
                'angkatan' => '2025',
                'user_id' => 1
            ],
            [
                'nama' => 'Deni Kurniawan',
                'nisn' => '1234567893',
                'nis' => '2025003',
                'jenis_kelamin' => 'Laki-laki',
                'jurusan_id' => $jurusanBd,
                'kelas' => 'X',
                'angkatan' => '2025',
                'user_id' => 1
            ],
            [
                'nama' => 'Siti Rahayu',
                'nisn' => '1234567894',
                'nis' => '2025004',
                'jenis_kelamin' => 'Perempuan',
                'jurusan_id' => $jurusanRpl,
                'kelas' => 'X',
                'angkatan' => '2025',
                'user_id' => 1
            ],
            [
                'nama' => 'Ahmad Fajar',
                'nisn' => '1234567895',
                'nis' => '2025005',
                'jenis_kelamin' => 'Laki-laki',
                'jurusan_id' => $jurusanAk,
                'kelas' => 'X',
                'angkatan' => '2025',
                'user_id' => 1
            ],
        ];        foreach ($siswas as $siswa) {
            Siswa::create($siswa);
        }
    }
}
