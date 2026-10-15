<?php

namespace Tests\Feature\Api;

use App\Models\Kavling;
use App\Models\Perumahan;
use App\Models\TipeRumah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    private TipeRumah $subsidi;

    private TipeRumah $premium;

    protected function setUp(): void
    {
        parent::setUp();

        $perumahan = Perumahan::factory()->create(['nama_perumahan' => 'Arkana Residence', 'lokasi' => 'Kec. Mayangan, Probolinggo']);
        $this->subsidi = TipeRumah::factory()->for($perumahan)->create(['nama_tipe' => 'Tipe 36/60', 'jenis_rumah' => 'subsidi', 'harga' => 162000000, 'cicilan' => 900000]);
        $this->premium = TipeRumah::factory()->for($perumahan)->create(['nama_tipe' => 'Tipe 60/100', 'jenis_rumah' => 'premium', 'harga' => 450000000, 'cicilan' => 3000000]);
        Kavling::factory()->for($this->subsidi)->create(['status' => 'tersedia']);
        Kavling::factory()->for($this->subsidi)->create(['status' => 'terjual']);
    }

    public function test_catalog_lists_projects_with_numeric_prices(): void
    {
        $this->getJson('/api/public/perumahan')
            ->assertOk()
            ->assertJsonPath('data.0.nama_perumahan', 'Arkana Residence')
            ->assertJsonPath('data.0.harga_mulai', 162000000)
            ->assertJsonPath('data.0.tipe_rumah.0.harga', 162000000)
            ->assertJsonPath('data.0.tipe_rumah.0.jenis_rumah', 'subsidi')
            ->assertJsonPath('data.0.tipe_rumah.0.unit_tersedia', 1)
            ->assertJsonCount(2, 'data.0.tipe_rumah');
    }

    public function test_catalog_filters_tipe_rumah(): void
    {
        $this->getJson('/api/public/perumahan?jenis_rumah=premium')
            ->assertOk()
            ->assertJsonCount(1, 'data.0.tipe_rumah')
            ->assertJsonPath('data.0.tipe_rumah.0.nama_tipe', 'Tipe 60/100');

        $this->getJson('/api/public/perumahan?max_cicilan=1000000')
            ->assertOk()
            ->assertJsonCount(1, 'data.0.tipe_rumah')
            ->assertJsonPath('data.0.tipe_rumah.0.nama_tipe', 'Tipe 36/60');

        $this->getJson('/api/public/perumahan?lokasi=mayangan')->assertJsonCount(1, 'data');
        $this->getJson('/api/public/perumahan?lokasi=wonoasih')->assertJsonCount(0, 'data');
        $this->getJson('/api/public/perumahan?min_budget=500000000')->assertJsonCount(0, 'data');
    }

    public function test_perumahan_and_tipe_detail(): void
    {
        $this->getJson('/api/public/perumahan/'.$this->subsidi->id_perumahan)
            ->assertOk()
            ->assertJsonPath('data.unit.total', 2)
            ->assertJsonPath('data.unit.terjual', 1)
            ->assertJsonCount(2, 'data.tipe_rumah.0.kavling');

        $this->getJson('/api/public/tipe-rumah/'.$this->subsidi->id)
            ->assertOk()
            ->assertJsonPath('data.nama_tipe', 'Tipe 36/60')
            ->assertJsonPath('data.perumahan.nama_perumahan', 'Arkana Residence')
            ->assertJsonMissingPath('data.kavling.0.leads');

        $this->getJson('/api/public/perumahan/999')->assertNotFound();
        $this->getJson('/api/public/tipe-rumah/999')->assertNotFound();
    }

    public function test_filter_options(): void
    {
        $this->getJson('/api/public/filters')
            ->assertOk()
            ->assertJsonPath('data.lokasi.0', 'Kec. Mayangan, Probolinggo')
            ->assertJsonPath('data.harga.min', 162000000)
            ->assertJsonCount(2, 'data.jenis_rumah');
    }
}
