<?php

namespace Tests\Feature\Api;

use App\Models\Leads;
use App\Models\TipeRumah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicLeadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_interest_form_without_ktp_creates_pending_lead(): void
    {
        $tipe = TipeRumah::factory()->create();

        $this->postJson('/api/public/leads', [
            'id_tipe' => $tipe->id,
            'nama_calon' => 'Budi Santoso',
            'no_telp' => '+62 812-3456-7890',
            'pesan' => 'Ingin survey',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status_keseriusan', 'pending')
            ->assertJsonPath('data.has_ktp', false)
            ->assertJsonPath('data.id_perumahan', $tipe->id_perumahan)
            ->assertJsonPath('data.no_telp', '081234567890');
    }

    public function test_interest_form_ignores_ktp_upload(): void
    {
        Storage::fake('local');
        $tipe = TipeRumah::factory()->create();

        $this->post('/api/public/leads', [
            'id_perumahan' => $tipe->id_perumahan,
            'nama_calon' => 'Siti',
            'no_telp' => '089876543210',
            'file_ktp' => UploadedFile::fake()->image('ktp.jpg'),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.has_ktp', false);

        $this->assertNull(Leads::first()->file_ktp);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_interest_form_requires_project_name_and_phone(): void
    {
        $this->postJson('/api/public/leads', [])
            ->assertStatus(422)
            ->assertJsonStructure(['data' => ['id_perumahan', 'nama_calon', 'no_telp']]);
    }
}
