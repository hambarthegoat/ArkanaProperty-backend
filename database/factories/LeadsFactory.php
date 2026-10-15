<?php

namespace Database\Factories;

use App\Models\Leads;
use App\Models\Perumahan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Leads>
 */
class LeadsFactory extends Factory
{
    protected $model = Leads::class;

    public function definition(): array
    {
        return [
            'id_perumahan' => Perumahan::factory(),
            'nama_calon' => fake()->name(),
            'no_telp' => '08'.fake()->unique()->numerify('##########'),
            'status_keseriusan' => 'pending',
        ];
    }

    public function withKtp(): static
    {
        return $this->state(['file_ktp' => 'ktp/contoh.jpg']);
    }
}
