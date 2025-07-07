<?php

namespace Database\Factories;

use App\Models\Jurusan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jurusan>
 */
class JurusanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Jurusan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => $this->faker->randomElement([
                'Teknik Komputer dan Jaringan',
                'Rekayasa Perangkat Lunak',
                'Multimedia',
                'Teknik Kendaraan Ringan',
                'Teknik Sepeda Motor',
                'Akuntansi',
                'Administrasi Perkantoran',
                'Pemasaran'
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 