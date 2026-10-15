<?php

namespace Database\Factories;

use App\Models\Kavling;
use App\Models\TipeRumah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kavling>
 */
class KavlingFactory extends Factory
{
    protected $model = Kavling::class;

    public function definition(): array
    {
        return [
            'id_tipe' => TipeRumah::factory(),
            'blok' => 'Blok A',
            'nomor_kavling' => 'A-'.fake()->unique()->numerify('##'),
            'status' => 'tersedia',
        ];
    }
}
