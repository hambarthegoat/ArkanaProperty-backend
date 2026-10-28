<?php

namespace Tests\Feature\Api;

use App\Models\Akun;
use App\Models\Kavling;
use App\Models\Leads;
use App\Models\TipeRumah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    private Akun $marketing;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(Akun::factory()->admin()->create());
        $this->marketing = Akun::factory()->marketing()->create(['no_telp' => '081211112222']);
    }

    public function test_non_admin_is_forbidden(): void
    {
        Sanctum::actingAs($this->marketing);

        $this->getJson('/api/admin/leads')->assertForbidden()->assertJsonPath('success', false);
    }

    public function test_validation_requires_ktp(): void
    {
        $lead = Leads::factory()->create();

        $this->postJson("/api/admin/leads/{$lead->id}/validate", ['is_valid' => true, 'id_marketing' => $this->marketing->id])
            ->assertStatus(422)
            ->assertJsonPath('message', 'KTP belum diunggah. Lengkapi KTP sebelum memvalidasi lead.');
    }

    public function test_validate_creates_phone_based_account_for_lead_with_ktp(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ktp/contoh.jpg', 'ktp');
        $lead = Leads::factory()->withKtp()->create(['no_telp' => '081234567890']);

        $this->get("/api/admin/leads/{$lead->id}/ktp")->assertOk();

        $response = $this->postJson("/api/admin/leads/{$lead->id}/validate", ['is_valid' => true, 'id_marketing' => $this->marketing->id])
            ->assertOk()
            ->assertJsonPath('data.lead.status_keseriusan', 'validated')
            ->assertJsonPath('data.lead.marketing.id', $this->marketing->id)
            ->assertJsonPath('data.kredensial.username', '081234567890')
            ->assertJsonPath('data.kredensial.akun_baru', true);

        $password = $response->json('data.kredensial.password');
        $this->assertSame(8, strlen($password));
        $this->assertNotSame('081234567890', $password);

        $this->postJson('/api/auth/login', ['username' => '081234567890', 'password' => $password])
            ->assertOk()
            ->assertJsonPath('data.akun.role', 'leads');
    }

    public function test_validation_defaults_to_the_assigned_marketing(): void
    {
        $lead = Leads::factory()->withKtp()->create(['no_telp' => '081234567890', 'id_marketing' => $this->marketing->id]);

        $this->postJson("/api/admin/leads/{$lead->id}/validate", ['is_valid' => true])
            ->assertOk()
            ->assertJsonPath('data.lead.marketing.id', $this->marketing->id);
    }

    public function test_validation_needs_an_assigned_marketing(): void
    {
        $lead = Leads::factory()->withKtp()->create();

        $this->postJson("/api/admin/leads/{$lead->id}/validate", ['is_valid' => true])
            ->assertStatus(422)
            ->assertJsonStructure(['data' => ['id_marketing']]);
    }

    public function test_admin_assigns_pending_lead_to_marketing(): void
    {
        $lead = Leads::factory()->create(['status_keseriusan' => 'pending']);

        $this->postJson("/api/admin/leads/{$lead->id}/assign", ['id_marketing' => $this->marketing->id])
            ->assertOk()
            ->assertJsonPath('data.id_marketing', $this->marketing->id)
            ->assertJsonPath('data.status_keseriusan', 'pending')
            ->assertJsonPath('data.marketing.id', $this->marketing->id);

        $other = Akun::factory()->marketing()->create();
        $this->postJson("/api/admin/leads/{$lead->id}/assign", ['id_marketing' => $other->id])
            ->assertOk()
            ->assertJsonPath('data.id_marketing', $other->id);
    }

    public function test_assign_rejects_non_marketing_account_and_missing_input(): void
    {
        $lead = Leads::factory()->create();
        $leadsAkun = Akun::factory()->leads()->create();

        $this->postJson("/api/admin/leads/{$lead->id}/assign", ['id_marketing' => $leadsAkun->id])->assertStatus(422);
        $this->postJson("/api/admin/leads/{$lead->id}/assign", [])->assertStatus(422)->assertJsonStructure(['data' => ['id_marketing']]);
        $this->postJson("/api/admin/leads/{$lead->id}/assign", ['id_marketing' => 999999])->assertStatus(422);
        $this->postJson('/api/admin/leads/999999/assign', ['id_marketing' => $this->marketing->id])->assertNotFound();

        $this->assertNull($lead->fresh()->id_marketing);
    }

    public function test_assign_refuses_validated_and_rejected_leads(): void
    {
        $validated = Leads::factory()->create(['status_keseriusan' => 'validated', 'id_marketing' => $this->marketing->id]);
        $rejected = Leads::factory()->create(['status_keseriusan' => 'rejected']);
        $other = Akun::factory()->marketing()->create();

        $this->postJson("/api/admin/leads/{$validated->id}/assign", ['id_marketing' => $other->id])->assertStatus(422);
        $this->postJson("/api/admin/leads/{$rejected->id}/assign", ['id_marketing' => $other->id])->assertStatus(422);

        $this->assertSame($this->marketing->id, $validated->fresh()->id_marketing);
        $this->assertNull($rejected->fresh()->id_marketing);
    }

    public function test_marketing_cannot_assign_leads(): void
    {
        $lead = Leads::factory()->create();
        Sanctum::actingAs($this->marketing);

        $this->postJson("/api/admin/leads/{$lead->id}/assign", ['id_marketing' => $this->marketing->id])->assertForbidden();
    }

    public function test_admin_can_no_longer_upload_ktp(): void
    {
        Storage::fake('local');
        $lead = Leads::factory()->create();

        $this->post("/api/admin/leads/{$lead->id}/ktp", ['file_ktp' => UploadedFile::fake()->image('ktp.png')], ['Accept' => 'application/json'])
            ->assertStatus(405);

        $this->assertFalse($lead->fresh()->hasKtp());
    }

    public function test_repeat_lead_reuses_existing_portal_account(): void
    {
        $akun = Akun::factory()->leads()->create(['no_telp' => '081234567890']);
        $lead = Leads::factory()->withKtp()->create(['no_telp' => '081234567890']);

        $this->postJson("/api/admin/leads/{$lead->id}/validate", ['is_valid' => true, 'id_marketing' => $this->marketing->id])
            ->assertOk()
            ->assertJsonPath('data.kredensial.akun_baru', false)
            ->assertJsonPath('data.kredensial.password', null)
            ->assertJsonPath('data.lead.id_akun_leads', $akun->id);
    }

    public function test_phone_owned_by_staff_account_is_rejected(): void
    {
        $lead = Leads::factory()->withKtp()->create(['no_telp' => $this->marketing->no_telp]);
        $this->marketing->update(['username' => $this->marketing->no_telp]);

        $this->postJson("/api/admin/leads/{$lead->id}/validate", ['is_valid' => true, 'id_marketing' => $this->marketing->id])
            ->assertStatus(422);
    }

    public function test_reject_lead(): void
    {
        $lead = Leads::factory()->create();

        $this->postJson("/api/admin/leads/{$lead->id}/validate", ['is_valid' => false])
            ->assertOk()
            ->assertJsonPath('data.status_keseriusan', 'rejected');
    }

    public function test_leads_index_filters(): void
    {
        Leads::factory()->create(['nama_calon' => 'Ahmad Rizky']);
        Leads::factory()->create(['nama_calon' => 'Siti', 'status_keseriusan' => 'rejected']);

        $this->getJson('/api/admin/leads?search=ahmad')->assertJsonCount(1, 'data')->assertJsonPath('data.0.nama_calon', 'Ahmad Rizky');
        $this->getJson('/api/admin/leads?status=rejected')->assertJsonCount(1, 'data')->assertJsonPath('data.0.nama_calon', 'Siti');
    }

    public function test_kavling_listing_and_status_updates(): void
    {
        $tipe = TipeRumah::factory()->create();
        $lead = Leads::factory()->create(['nama_calon' => 'Ahmad Rizky']);

        $this->postJson("/api/admin/tipe-rumah/{$tipe->id}/kavling", ['blok' => 'Blok A', 'nomor_kavling' => 'A-01'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'tersedia');

        $kavling = Kavling::first();

        $this->putJson("/api/admin/kavling/{$kavling->id}", ['status' => 'booking', 'id_leads' => $lead->id])
            ->assertOk()
            ->assertJsonPath('data.status', 'booking')
            ->assertJsonPath('data.leads.nama_calon', 'Ahmad Rizky');

        $this->getJson('/api/admin/kavling?status=booking&id_perumahan='.$tipe->id_perumahan)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tipe_rumah.perumahan.id', $tipe->id_perumahan);
        $this->getJson('/api/admin/kavling?search=ahmad')->assertJsonCount(1, 'data');

        $this->putJson("/api/admin/kavling/{$kavling->id}", ['status' => 'tersedia', 'id_leads' => $lead->id])
            ->assertOk()
            ->assertJsonPath('data.leads', null);

        $this->putJson("/api/admin/kavling/{$kavling->id}", ['status' => 'dijual'])->assertStatus(422);
    }

    public function test_perumahan_store_and_index_with_unit_counts(): void
    {
        $this->postJson('/api/admin/perumahan', [
            'nama_perumahan' => 'Griya Asri',
            'lokasi' => 'Kec. Kademangan',
            'tipe_rumah' => [['nama_tipe' => 'Tipe 36', 'jenis_rumah' => 'subsidi', 'harga' => 150000000, 'luas_tanah' => 60]],
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'aktif')
            ->assertJsonPath('data.unit.total', 0)
            ->assertJsonPath('data.tipe_rumah.0.luas_tanah', 60);

        $this->getJson('/api/admin/perumahan')->assertOk()->assertJsonPath('data.0.unit.tersedia', 0);
    }

    public function test_dashboard_summary(): void
    {
        Leads::factory()->count(2)->create();
        Kavling::factory()->create(['status' => 'terjual']);

        $this->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.leads.pending', 2)
            ->assertJsonPath('data.unit.terjual', 1)
            ->assertJsonPath('data.marketing', 1)
            ->assertJsonCount(2, 'data.leads_terbaru');
    }

    public function test_marketing_accounts(): void
    {
        $this->postJson('/api/admin/marketing', ['username' => 'santi', 'password' => 'rahasia', 'nama' => 'Santi', 'no_telp' => '+6281233334444'])
            ->assertCreated()
            ->assertJsonPath('data.no_telp', '081233334444')
            ->assertJsonMissingPath('data.password');

        $this->getJson('/api/admin/marketing')->assertJsonCount(2, 'data');
    }
}
