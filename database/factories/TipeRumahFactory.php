<?php

namespace Database\Factories;

use App\Models\Perumahan;
use App\Models\TipeRumah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipeRumah>
 */
class TipeRumahFactory extends Factory
{
    protected $model = TipeRumah::class;

    public function definition(): array
    {
        return [
            'id_perumahan' => Perumahan::factory(),
            'nama_tipe' => 'Tipe '.fake()->randomElement(['36/60', '45/84', '60/100']),
            'jenis_rumah' => 'subsidi',
            'harga' => 162000000,
            'dp' => 5000000,
            'cicilan' => 900000,
            'luas_tanah' => 60,
            'luas_bangunan' => 36,
            'kamar_tidur' => 2,
            'kamar_mandi' => 1,
            'spesifikasi' => '2 KT, 1 KM, Dapur, Carport',
        ];
    }
}
