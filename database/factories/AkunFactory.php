<?php

namespace Database\Factories;

use App\Models\Akun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Akun>
 */
class AkunFactory extends Factory
{
    protected $model = Akun::class;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'password' => 'password',
            'role' => 'marketing',
            'nama' => fake()->name(),
            'no_telp' => '08'.fake()->unique()->numerify('##########'),
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }

    public function marketing(): static
    {
        return $this->state(['role' => 'marketing']);
    }

    public function leads(): static
    {
        return $this->state([
            'role' => 'leads',
            'username' => fn (array $attributes) => $attributes['no_telp'],
        ]);
    }
}
