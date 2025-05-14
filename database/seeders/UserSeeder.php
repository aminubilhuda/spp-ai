<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a new user
        User::create([
            'name' => 'Operator',
            'akses' => 'operator',
            'nohp' => '081234567890',
            'nohp_verified_at' => now(),
            'email' => 'operator@operator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('operator'),
        ]);

        // Create a new user
        User::create([
            'name' => 'Operator2',
            'akses' => 'operator',
            'nohp' => '081298788990',
            'nohp_verified_at' => now(),
            'email' => 'operator2@operator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('operator'),
        ]);

        // Create a new user
        User::create([
            'name' => 'Wali Murid',
            'akses' => 'wali',
            'nohp' => '089876543210',
            'nohp_verified_at' => now(),
            'email' => 'wali@wali.com',
            'email_verified_at' => now(),
            'password' => Hash::make('wali'),
        ]);
    }
}