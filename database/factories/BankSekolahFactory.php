<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BankSekolah>
 */
class BankSekolahFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $banks = [
            ['kode' => '014', 'nama' => 'Bank BCA'],
            ['kode' => '008', 'nama' => 'Bank Mandiri'],
            ['kode' => '009', 'nama' => 'Bank BNI'],
            ['kode' => '002', 'nama' => 'Bank BRI'],
            ['kode' => '022', 'nama' => 'Bank CIMB Niaga'],
            ['kode' => '011', 'nama' => 'Bank Danamon'],
            ['kode' => '013', 'nama' => 'Bank Permata'],
            ['kode' => '016', 'nama' => 'Bank BII'],
        ];

        $selectedBank = $this->faker->randomElement($banks);

        return [
            'kode_bank' => $selectedBank['kode'],
            'nama_bank' => $selectedBank['nama'],
            'no_rekening' => $this->faker->numerify('##########'),
            'atas_nama' => $this->faker->company(),
            'keterangan' => $this->faker->optional()->sentence(),
        ];
    }
}
