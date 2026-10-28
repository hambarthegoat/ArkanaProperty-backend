<?php

namespace Tests\Feature\Api;

use App\Models\Akun;
use App\Models\Leads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MarketingApiTest extends TestCase
{
    use RefreshDatabase;

    private Akun $marketing;

    private Leads $lead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->marketing = Akun::factory()->marketing()->create();
        $akunLeads = Akun::factory()->leads()->create();
        $this->lead = Leads::factory()->create([
            'id_marketing' => $this->marketing->id,
            'id_akun_leads' => $akunLeads->id,
            'status_keseriusan' => 'validated',
        ]);
        Leads::factory()->create(['id_marketing' => Akun::factory()->marketing()->create()->id]);

        Sanctum::actingAs($this->marketing);
    }

    public function test_only_assigned_leads_are_visible(): void
    {
        $this->getJson('/api/marketing/leads')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->lead->id);

        $other = Leads::where('id', '!=', $this->lead->id)->first();
        $this->getJson("/api/marketing/leads/{$other->id}")->assertNotFound();
    }

    public function test_assigned_pending_lead_is_listed_and_accepts_ktp(): void
    {
        Storage::fake('local');
        $pending = Leads::factory()->create(['status_keseriusan' => 'pending', 'id_marketing' => $this->marketing->id]);

        $ids = collect($this->getJson('/api/marketing/leads')->assertOk()->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($pending->id));

        $this->post("/api/marketing/leads/{$pending->id}/ktp", ['file_ktp' => UploadedFile::fake()->image('ktp.png')], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.has_ktp', true)
            ->assertJsonPath('data.status_keseriusan', 'pending')
            ->assertJsonMissingPath('data.file_ktp');

        Storage::disk('local')->assertExists($pending->fresh()->file_ktp);
    }

    public function test_unassigned_pending_lead_is_hidden_and_cannot_receive_ktp(): void
    {
        Storage::fake('local');
        $unassigned = Leads::factory()->create(['status_keseriusan' => 'pending', 'id_marketing' => null]);

        $this->getJson('/api/marketing/leads')->assertOk()->assertJsonCount(1, 'data');

        $this->post("/api/marketing/leads/{$unassigned->id}/ktp", ['file_ktp' => UploadedFile::fake()->image('ktp.png')], ['Accept' => 'application/json'])
            ->assertNotFound();

        $this->assertNull($unassigned->fresh()->id_marketing);
        $this->assertFalse($unassigned->fresh()->hasKtp());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_replacing_ktp_removes_the_old_file(): void
    {
        Storage::fake('local');
        $this->post("/api/marketing/leads/{$this->lead->id}/ktp", ['file_ktp' => UploadedFile::fake()->image('a.png')], ['Accept' => 'application/json'])->assertOk();
        $first = $this->lead->fresh()->file_ktp;

        $this->post("/api/marketing/leads/{$this->lead->id}/ktp", ['file_ktp' => UploadedFile::fake()->image('b.png')], ['Accept' => 'application/json'])->assertOk();

        Storage::disk('local')->assertMissing($first);
        Storage::disk('local')->assertExists($this->lead->fresh()->file_ktp);
    }

    public function test_cannot_upload_ktp_to_another_marketings_lead(): void
    {
        Storage::fake('local');
        $other = Leads::where('id', '!=', $this->lead->id)->first();

        $this->post("/api/marketing/leads/{$other->id}/ktp", ['file_ktp' => UploadedFile::fake()->image('ktp.png')], ['Accept' => 'application/json'])
            ->assertNotFound();

        $this->assertFalse($other->fresh()->hasKtp());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_cannot_upload_ktp_to_own_rejected_lead(): void
    {
        Storage::fake('local');
        $lead = Leads::factory()->create(['status_keseriusan' => 'rejected', 'id_marketing' => $this->marketing->id]);

        $this->post("/api/marketing/leads/{$lead->id}/ktp", ['file_ktp' => UploadedFile::fake()->image('ktp.png')], ['Accept' => 'application/json'])
            ->assertStatus(422);

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_ktp_upload_rejects_missing_or_invalid_files(): void
    {
        Storage::fake('local');

        $this->postJson("/api/marketing/leads/{$this->lead->id}/ktp", [])->assertStatus(422);
        $this->post("/api/marketing/leads/{$this->lead->id}/ktp", ['file_ktp' => UploadedFile::fake()->create('x.exe', 10)], ['Accept' => 'application/json'])
            ->assertStatus(422);
        $this->post("/api/marketing/leads/{$this->lead->id}/ktp", ['file_ktp' => UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf')], ['Accept' => 'application/json'])
            ->assertStatus(422);
        $this->post('/api/marketing/leads/999999/ktp', ['file_ktp' => UploadedFile::fake()->image('k.png')], ['Accept' => 'application/json'])
            ->assertNotFound();
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_ktp_upload_is_marketing_only(): void
    {
        Sanctum::actingAs(Akun::factory()->admin()->create());

        $this->post("/api/marketing/leads/{$this->lead->id}/ktp", ['file_ktp' => UploadedFile::fake()->image('ktp.png')], ['Accept' => 'application/json'])
            ->assertForbidden();
    }

    public function test_update_priority_and_notes(): void
    {
        $this->patchJson("/api/marketing/leads/{$this->lead->id}", ['prioritas' => 'tinggi', 'catatan' => 'Siap survey'])
            ->assertOk()
            ->assertJsonPath('data.prioritas', 'tinggi')
            ->assertJsonPath('data.catatan', 'Siap survey');
    }

    public function test_create_and_list_agenda_by_lead_id(): void
    {
        $this->postJson('/api/marketing/calendar', [
            'id_lead' => $this->lead->id,
            'judul' => 'Survey Lokasi',
            'jenis' => 'survey',
            'tanggal_kegiatan' => now()->addDay()->format('Y-m-d H:i:s'),
            'lokasi' => 'Blok A-01',
        ])
            ->assertCreated()
            ->assertJsonPath('data.id_leads', $this->lead->id_akun_leads)
            ->assertJsonPath('data.jenis', 'survey');

        $this->getJson('/api/marketing/calendar?from='.now()->toDateString())
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.leads.id', $this->lead->id_akun_leads);
    }

    public function test_agenda_for_unassigned_lead_is_rejected(): void
    {
        $other = Leads::where('id', '!=', $this->lead->id)->first();

        $this->postJson('/api/marketing/calendar', ['id_lead' => $other->id, 'tanggal_kegiatan' => now()->addDay()->toDateTimeString()])
            ->assertStatus(422);
    }

    public function test_submit_and_list_reports(): void
    {
        $this->postJson('/api/marketing/reports', [
            'tanggal_report' => now()->toDateString(),
            'id_lead' => $this->lead->id,
            'jenis_aktivitas' => 'survey_lokasi',
            'deskripsi_progres' => 'Klien tertarik.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.leads.id', $this->lead->id)
            ->assertJsonPath('data.jenis_aktivitas_label', 'Survey Lokasi');

        $this->getJson('/api/marketing/reports')->assertOk()->assertJsonCount(1, 'data');
    }
}
