<?php

namespace Database\Factories;

use App\Models\Akun;
use App\Models\Kalender;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kalender>
 */
class KalenderFactory extends Factory
{
    protected $model = Kalender::class;

    public function definition(): array
    {
        return [
            'id_marketing' => Akun::factory()->marketing(),
            'id_leads' => Akun::factory()->leads(),
            'judul' => 'Survey Lokasi',
            'jenis' => 'survey',
            'tanggal_kegiatan' => now()->addDay(),
            'lokasi' => 'Blok A-01',
            'deskripsi_agenda' => fake()->sentence(),
        ];
    }
}
