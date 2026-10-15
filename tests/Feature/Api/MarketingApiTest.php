<?php

namespace Tests\Feature\Api;

use App\Models\Akun;
use App\Models\Leads;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
