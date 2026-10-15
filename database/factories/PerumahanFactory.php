<?php

namespace Database\Factories;

use App\Models\Perumahan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Perumahan>
 */
class PerumahanFactory extends Factory
{
    protected $model = Perumahan::class;

    public function definition(): array
    {
        return [
            'nama_perumahan' => 'Arkana '.fake()->unique()->city(),
            'lokasi' => 'Kec. '.fake()->city().', Probolinggo',
            'deskripsi' => fake()->sentence(12),
            'status' => 'aktif',
        ];
    }
}
