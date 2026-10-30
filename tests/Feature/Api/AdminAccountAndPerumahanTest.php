<?php

namespace Tests\Feature\Api;

use App\Models\Akun;
use App\Models\Kavling;
use App\Models\Leads;
use App\Models\Perumahan;
use App\Models\TipeRumah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminAccountAndPerumahanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(Akun::factory()->admin()->create());
    }

    // ---- Marketing accounts

    public function test_admin_creates_marketing_account_that_can_log_in(): void
    {
        $this->postJson('/api/admin/marketing', [
            'username' => 'rina',
            'password' => 'rahasia123',
            'nama' => 'Rina Marketing',
            'no_telp' => '+62 812-9999-0001',
        ])
            ->assertCreated()
            ->assertJsonPath('data.role', 'marketing')
            ->assertJsonPath('data.no_telp', '081299990001')
            ->assertJsonMissingPath('data.password');

        $this->assertNotSame('rahasia123', Akun::where('username', 'rina')->value('password'));

        $this->app['auth']->forgetGuards();
        $this->postJson('/api/auth/login', ['username' => 'rina', 'password' => 'rahasia123'])
            ->assertOk()
            ->assertJsonPath('data.akun.role', 'marketing');
    }

    public function test_marketing_account_validation(): void
    {
        Akun::factory()->marketing()->create(['username' => 'dimas']);

        $this->postJson('/api/admin/marketing', ['username' => 'dimas', 'password' => 'rahasia123'])
            ->assertStatus(422)->assertJsonStructure(['data' => ['username']]);
        $this->postJson('/api/admin/marketing', ['username' => 'baru', 'password' => '123'])
            ->assertStatus(422)->assertJsonStructure(['data' => ['password']]);
        $this->postJson('/api/admin/marketing', [])
            ->assertStatus(422)->assertJsonStructure(['data' => ['username', 'password']]);
    }

    public function test_marketing_list_and_create_are_admin_only(): void
    {
        Akun::factory()->marketing()->create(['username' => 'dimas', 'nama' => 'Dimas']);

        $this->getJson('/api/admin/marketing')->assertOk()->assertJsonPath('data.0.username', 'dimas');

        Sanctum::actingAs(Akun::factory()->marketing()->create());
        $this->getJson('/api/admin/marketing')->assertForbidden();
        $this->postJson('/api/admin/marketing', ['username' => 'x', 'password' => 'rahasia123'])->assertForbidden();
    }

    // ---- Perumahan create

    public function test_create_perumahan_without_house_type(): void
    {
        $this->postJson('/api/admin/perumahan', ['nama_perumahan' => 'Arkana Baru', 'lokasi' => 'Kec. Mayangan'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'aktif')
            ->assertJsonCount(0, 'data.tipe_rumah');
    }

    public function test_create_perumahan_with_first_house_type(): void
    {
        $this->postJson('/api/admin/perumahan', [
            'nama_perumahan' => 'Arkana Baru', 'lokasi' => 'Kec. Mayangan',
            'tipe_rumah' => [['nama_tipe' => 'Tipe 36/60', 'jenis_rumah' => 'subsidi', 'harga' => 162000000]],
        ])->assertCreated()->assertJsonPath('data.tipe_rumah.0.nama_tipe', 'Tipe 36/60');
    }

    // ---- Perumahan update

    public function test_update_perumahan_details_keeps_other_fields(): void
    {
        $p = Perumahan::factory()->create(['nama_perumahan' => 'Lama', 'lokasi' => 'Kec. A', 'status' => 'aktif']);

        $this->putJson("/api/admin/perumahan/{$p->id}", ['nama_perumahan' => 'Baru', 'status' => 'pre_launch'])
            ->assertOk()
            ->assertJsonPath('data.nama_perumahan', 'Baru')
            ->assertJsonPath('data.status', 'pre_launch')
            ->assertJsonPath('data.lokasi', 'Kec. A');
    }

    public function test_update_perumahan_validation_and_not_found(): void
    {
        $p = Perumahan::factory()->create();

        $this->putJson("/api/admin/perumahan/{$p->id}", ['status' => 'bukan-status'])->assertStatus(422);
        $this->putJson("/api/admin/perumahan/{$p->id}", ['nama_perumahan' => ''])->assertStatus(422);
        $this->putJson('/api/admin/perumahan/999999', ['nama_perumahan' => 'X'])->assertNotFound();
    }

    public function test_update_replaces_image_and_removes_the_old_file(): void
    {
        Storage::fake('public');
        $p = Perumahan::factory()->create();

        $this->post("/api/admin/perumahan/{$p->id}", ['_method' => 'PUT', 'gambar' => UploadedFile::fake()->image('a.jpg')], ['Accept' => 'application/json'])->assertOk();
        $first = $p->fresh()->gambar;
        Storage::disk('public')->assertExists($first);

        $this->post("/api/admin/perumahan/{$p->id}", ['_method' => 'PUT', 'gambar' => UploadedFile::fake()->image('b.jpg')], ['Accept' => 'application/json'])->assertOk();

        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($p->fresh()->gambar);
    }

    public function test_update_never_touches_an_external_image_url(): void
    {
        Storage::fake('public');
        $p = Perumahan::factory()->create(['gambar' => 'https://example.com/foto.jpg']);

        $this->post("/api/admin/perumahan/{$p->id}", ['_method' => 'PUT', 'gambar' => UploadedFile::fake()->image('a.jpg')], ['Accept' => 'application/json'])->assertOk();

        $this->assertNotSame('https://example.com/foto.jpg', $p->fresh()->gambar);
    }

    // ---- Perumahan delete

    public function test_delete_perumahan_removes_types_units_and_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('perumahan/foto.jpg', 'x');
        $p = Perumahan::factory()->create(['gambar' => 'perumahan/foto.jpg']);
        $tipe = TipeRumah::factory()->create(['id_perumahan' => $p->id]);
        $kavling = Kavling::factory()->create(['id_tipe' => $tipe->id]);

        $this->deleteJson("/api/admin/perumahan/{$p->id}")->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseMissing('tb_perumahan', ['id' => $p->id]);
        $this->assertDatabaseMissing('tb_tipe_rumah', ['id' => $tipe->id]);
        $this->assertDatabaseMissing('tb_kavling', ['id' => $kavling->id]);
        Storage::disk('public')->assertMissing('perumahan/foto.jpg');
    }

    public function test_delete_with_external_image_url_does_not_touch_storage(): void
    {
        Storage::fake('public');
        $p = Perumahan::factory()->create(['gambar' => 'https://example.com/foto.jpg']);

        $this->deleteJson("/api/admin/perumahan/{$p->id}")->assertOk();

        $this->assertDatabaseMissing('tb_perumahan', ['id' => $p->id]);
    }

    public function test_delete_is_refused_when_the_project_has_leads(): void
    {
        $p = Perumahan::factory()->create();
        Leads::factory()->create(['id_perumahan' => $p->id]);

        $this->deleteJson("/api/admin/perumahan/{$p->id}")
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('tb_perumahan', ['id' => $p->id]);
        $this->assertSame(1, Leads::count());
    }

    public function test_delete_is_refused_when_units_are_booked_or_sold(): void
    {
        $p = Perumahan::factory()->create();
        $tipe = TipeRumah::factory()->create(['id_perumahan' => $p->id]);
        Kavling::factory()->create(['id_tipe' => $tipe->id, 'status' => 'terjual']);

        $this->deleteJson("/api/admin/perumahan/{$p->id}")->assertStatus(422);

        $this->assertDatabaseHas('tb_perumahan', ['id' => $p->id]);
    }

    public function test_deletion_blocker_is_shared_by_the_model(): void
    {
        $free = Perumahan::factory()->create();
        $withLead = Perumahan::factory()->create();
        Leads::factory()->create(['id_perumahan' => $withLead->id]);
        $booked = Perumahan::factory()->create();
        Kavling::factory()->create(['id_tipe' => TipeRumah::factory()->create(['id_perumahan' => $booked->id])->id, 'status' => 'booking']);
        $available = Perumahan::factory()->create();
        Kavling::factory()->create(['id_tipe' => TipeRumah::factory()->create(['id_perumahan' => $available->id])->id, 'status' => 'tersedia']);

        $this->assertNull($free->deletionBlocker());
        $this->assertNull($available->deletionBlocker());
        $this->assertStringContainsString('1 lead', $withLead->deletionBlocker());
        $this->assertStringContainsString('booking atau terjual', $booked->deletionBlocker());
    }

    public function test_delete_not_found_and_admin_only(): void
    {
        $p = Perumahan::factory()->create();

        $this->deleteJson('/api/admin/perumahan/999999')->assertNotFound();

        Sanctum::actingAs(Akun::factory()->marketing()->create());
        $this->deleteJson("/api/admin/perumahan/{$p->id}")->assertForbidden();
        $this->putJson("/api/admin/perumahan/{$p->id}", ['nama_perumahan' => 'X'])->assertForbidden();
        $this->assertDatabaseHas('tb_perumahan', ['id' => $p->id]);
    }
}
