<?php

namespace Database\Seeders;

use App\Models\Akun;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Akun::firstOrCreate(
            ['username' => 'admin'],
            [
                'password' => 'password',
                'role' => 'admin',
                'nama' => 'Administrator',
            ]
        );
    }
}
