<?php

/*
 * This file is part of the IndoBank package.
 *
 * (c) Andri Desmana <andridesmana.pw | andridesmana29@gmail.com>
 *
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndoBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @deprecated
     *
     * @return void
     */
    public function run()
    {
        // Data bank Indonesia yang umum digunakan
        $banks = [
            ['sandi_bank' => '014', 'nama_bank' => 'Bank BCA'],
            ['sandi_bank' => '008', 'nama_bank' => 'Bank Mandiri'],
            ['sandi_bank' => '009', 'nama_bank' => 'Bank BNI'],
            ['sandi_bank' => '002', 'nama_bank' => 'Bank BRI'],
            ['sandi_bank' => '022', 'nama_bank' => 'Bank CIMB Niaga'],
            ['sandi_bank' => '011', 'nama_bank' => 'Bank Danamon'],
            ['sandi_bank' => '013', 'nama_bank' => 'Bank Permata'],
            ['sandi_bank' => '016', 'nama_bank' => 'Bank BII'],
            ['sandi_bank' => '019', 'nama_bank' => 'Bank Panin'],
            ['sandi_bank' => '023', 'nama_bank' => 'Bank UOB Indonesia'],
            ['sandi_bank' => '028', 'nama_bank' => 'Bank OCBC NISP'],
            ['sandi_bank' => '031', 'nama_bank' => 'Citibank'],
            ['sandi_bank' => '032', 'nama_bank' => 'Bank JTrust Indonesia'],
            ['sandi_bank' => '033', 'nama_bank' => 'Bank Mayapada'],
            ['sandi_bank' => '036', 'nama_bank' => 'Bank Artha Graha Internasional'],
            ['sandi_bank' => '037', 'nama_bank' => 'Bank Bukopin'],
            ['sandi_bank' => '040', 'nama_bank' => 'Bank HSBC Indonesia'],
            ['sandi_bank' => '045', 'nama_bank' => 'Bank BNI Syariah'],
            ['sandi_bank' => '046', 'nama_bank' => 'Bank Muamalat Indonesia'],
            ['sandi_bank' => '047', 'nama_bank' => 'Bank Syariah Mandiri'],
            ['sandi_bank' => '048', 'nama_bank' => 'Bank BRI Syariah'],
            ['sandi_bank' => '050', 'nama_bank' => 'Bank BCA Syariah'],
            ['sandi_bank' => '052', 'nama_bank' => 'Bank Tabungan Negara'],
            ['sandi_bank' => '053', 'nama_bank' => 'Bank Tabungan Pensiunan Nasional'],
        ];

        // Insert Data to Database
        DB::table('banks')->insert($banks);
    }
}