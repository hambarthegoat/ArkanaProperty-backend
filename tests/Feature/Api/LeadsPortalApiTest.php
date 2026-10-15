<?php

namespace Tests\Feature\Api;

use App\Models\Akun;
use App\Models\Kalender;
use App\Models\Kavling;
use App\Models\Leads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeadsPortalApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_timeline_contact_and_calendar(): void
    {
        $marketing = Akun::factory()->marketing()->create(['nama' => 'Dimas', 'no_telp' => '081211112222']);
        $akun = Akun::factory()->leads()->create();
        $lead = Leads::factory()->create([
            'id_marketing' => $marketing->id,
            'id_akun_leads' => $akun->id,
            'status_keseriusan' => 'validated',
        ]);
        Kavling::factory()->create(['status' => 'booking', 'id_leads' => $lead->id]);
        Kalender::factory()->create(['id_marketing' => $marketing->id, 'id_leads' => $akun->id, 'tanggal_kegiatan' => now()->addDay()]);
        Kalender::factory()->create(['id_marketing' => $marketing->id, 'id_leads' => $akun->id, 'tanggal_kegiatan' => now()->subDay()]);

        Sanctum::actingAs($akun);

        $this->getJson('/api/leads/profile')
            ->assertOk()
            ->assertJsonPath('data.lead.id', $lead->id)
            ->assertJsonPath('data.lead.kavling.status', 'booking')
            ->assertJsonPath('data.timeline.3.key', 'booking')
            ->assertJsonPath('data.timeline.3.status', 'completed')
            ->assertJsonPath('data.timeline.4.status', 'current');

        $this->getJson('/api/leads/marketing-contact')
            ->assertOk()
            ->assertJsonPath('data.nama', 'Dimas')
            ->assertJsonPath('data.wa_link', 'https://wa.me/6281211112222');

        $this->getJson('/api/leads/calendar')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/leads/calendar?semua=1')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_marketing_cannot_open_portal_endpoints(): void
    {
        Sanctum::actingAs(Akun::factory()->marketing()->create());

        $this->getJson('/api/leads/profile')->assertForbidden();
    }
}
