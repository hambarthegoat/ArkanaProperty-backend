<?php

namespace Database\Seeders;

use App\Models\Akun;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Akun::firstOrCreate(
            ['username' => 'admin'],
            [
                'password' => Hash::make('password'),
                'role' => 'admin',
                'nama' => 'Administrator',
            ]
        );
    }
}
