<?php

namespace Database\Factories;

use App\Models\Akun;
use App\Models\ReportMarketing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportMarketing>
 */
class ReportMarketingFactory extends Factory
{
    protected $model = ReportMarketing::class;

    public function definition(): array
    {
        return [
            'id_marketing' => Akun::factory()->marketing(),
            'tanggal_report' => now()->toDateString(),
            'jenis_aktivitas' => 'follow_up',
            'deskripsi_progres' => fake()->sentence(),
        ];
    }
}
