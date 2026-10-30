<?php

namespace Tests\Feature\Api;

use App\Models\Akun;
use App\Models\Blok;
use App\Models\Kavling;
use App\Models\Perumahan;
use App\Models\TipeRumah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BlokTest extends TestCase
{
    use RefreshDatabase;

    private Perumahan $perumahan;

    private TipeRumah $tipe;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(Akun::factory()->admin()->create());
        $this->perumahan = Perumahan::factory()->create();
        $this->tipe = TipeRumah::factory()->create(['id_perumahan' => $this->perumahan->id]);
    }

    private function addHouses(string $blok, string $nomor, int $jumlah = 1)
    {
        return $this->postJson("/api/admin/tipe-rumah/{$this->tipe->id}/kavling", ['blok' => $blok, 'nomor_kavling' => $nomor, 'jumlah_rumah' => $jumlah]);
    }

    public function test_admin_creates_an_empty_block(): void
    {
        $this->postJson("/api/admin/perumahan/{$this->perumahan->id}/blok", ['nama' => '  Blok A  '])
            ->assertCreated()
            ->assertJsonPath('data.nama', 'Blok A')
            ->assertJsonPath('data.id_perumahan', $this->perumahan->id);

        $this->assertDatabaseHas('tb_blok', ['id_perumahan' => $this->perumahan->id, 'nama' => 'Blok A']);
        $this->assertSame(0, Kavling::count());
    }

    public function test_block_name_is_required_and_unique_per_project(): void
    {
        Blok::create(['id_perumahan' => $this->perumahan->id, 'nama' => 'Blok A']);
        $other = Perumahan::factory()->create();

        $this->postJson("/api/admin/perumahan/{$this->perumahan->id}/blok", ['nama' => 'Blok A'])
            ->assertStatus(422)->assertJsonStructure(['data' => ['nama']]);
        $this->postJson("/api/admin/perumahan/{$this->perumahan->id}/blok", ['nama' => '   '])->assertStatus(422);
        $this->postJson("/api/admin/perumahan/{$this->perumahan->id}/blok", [])->assertStatus(422);
        $this->postJson("/api/admin/perumahan/{$this->perumahan->id}/blok", ['nama' => str_repeat('x', 51)])->assertStatus(422);
        $this->postJson("/api/admin/perumahan/{$other->id}/blok", ['nama' => 'Blok A'])->assertCreated();

        $this->assertSame(1, Blok::where('id_perumahan', $this->perumahan->id)->count());
    }

    public function test_create_block_for_unknown_project_is_404(): void
    {
        $this->postJson('/api/admin/perumahan/999999/blok', ['nama' => 'Blok A'])->assertNotFound();
        $this->getJson('/api/admin/perumahan/999999/blok')->assertNotFound();
    }

    public function test_one_block_holds_several_houses(): void
    {
        $this->postJson("/api/admin/perumahan/{$this->perumahan->id}/blok", ['nama' => 'Blok A'])->assertCreated();

        $this->addHouses('Blok A', 'A-01', 3)->assertCreated();
        $this->addHouses('Blok A', 'A-02')->assertCreated();

        $this->getJson("/api/admin/perumahan/{$this->perumahan->id}/blok")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama', 'Blok A')
            ->assertJsonPath('data.0.jumlah_rumah', 4);
    }

    public function test_adding_a_house_in_a_new_block_creates_the_block(): void
    {
        $this->addHouses('Blok Baru', 'B-01')->assertCreated();

        $this->assertDatabaseHas('tb_blok', ['id_perumahan' => $this->perumahan->id, 'nama' => 'Blok Baru']);

        // adding more houses to the same block does not duplicate it
        $this->addHouses('Blok Baru', 'B-02')->assertCreated();
        $this->assertSame(1, Blok::where('nama', 'Blok Baru')->count());
    }

    public function test_houses_without_a_block_create_no_block(): void
    {
        $this->postJson("/api/admin/tipe-rumah/{$this->tipe->id}/kavling", ['nomor_kavling' => 'X-01'])->assertCreated();

        $this->assertSame(0, Blok::count());
    }

    public function test_moving_a_house_to_another_block_creates_that_block(): void
    {
        $this->addHouses('Blok A', 'A-01')->assertCreated();
        $kavling = Kavling::first();

        $this->putJson("/api/admin/kavling/{$kavling->id}", ['status' => 'tersedia', 'blok' => 'Blok B'])->assertOk();

        $this->assertDatabaseHas('tb_blok', ['id_perumahan' => $this->perumahan->id, 'nama' => 'Blok B']);
    }

    public function test_block_counts_only_its_own_projects_houses(): void
    {
        $other = Perumahan::factory()->create();
        $otherTipe = TipeRumah::factory()->create(['id_perumahan' => $other->id]);
        $this->postJson("/api/admin/tipe-rumah/{$otherTipe->id}/kavling", ['blok' => 'Blok A', 'nomor_kavling' => 'A-01', 'jumlah_rumah' => 5])->assertCreated();
        $this->addHouses('Blok A', 'A-01', 2)->assertCreated();

        $this->getJson("/api/admin/perumahan/{$this->perumahan->id}/blok")->assertJsonPath('data.0.jumlah_rumah', 2);
        $this->getJson("/api/admin/perumahan/{$other->id}/blok")->assertJsonPath('data.0.jumlah_rumah', 5);
    }

    public function test_perumahan_endpoints_include_blocks_with_house_counts(): void
    {
        $this->postJson("/api/admin/perumahan/{$this->perumahan->id}/blok", ['nama' => 'Blok Kosong'])->assertCreated();
        $this->addHouses('Blok A', 'A-01', 2)->assertCreated();

        $blok = collect($this->getJson('/api/admin/perumahan')->assertOk()->json('data.0.blok'));
        $this->assertSame(['Blok A' => 2, 'Blok Kosong' => 0], $blok->pluck('jumlah_rumah', 'nama')->all());

        $this->getJson("/api/admin/perumahan/{$this->perumahan->id}")->assertJsonCount(2, 'data.blok');
    }

    public function test_empty_block_can_be_deleted_but_one_with_houses_cannot(): void
    {
        $empty = Blok::create(['id_perumahan' => $this->perumahan->id, 'nama' => 'Blok Kosong']);
        $this->addHouses('Blok A', 'A-01', 2)->assertCreated();
        $filled = Blok::where('nama', 'Blok A')->first();

        $this->deleteJson("/api/admin/blok/{$empty->id}")->assertOk();
        $this->assertDatabaseMissing('tb_blok', ['id' => $empty->id]);

        $this->deleteJson("/api/admin/blok/{$filled->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Blok A masih berisi 2 rumah sehingga tidak bisa dihapus.');
        $this->assertDatabaseHas('tb_blok', ['id' => $filled->id]);
        $this->assertSame(2, Kavling::count());

        $this->deleteJson('/api/admin/blok/999999')->assertNotFound();
    }

    public function test_block_endpoints_are_admin_only(): void
    {
        $blok = Blok::create(['id_perumahan' => $this->perumahan->id, 'nama' => 'Blok A']);
        Sanctum::actingAs(Akun::factory()->marketing()->create());

        $this->postJson("/api/admin/perumahan/{$this->perumahan->id}/blok", ['nama' => 'Blok B'])->assertForbidden();
        $this->getJson("/api/admin/perumahan/{$this->perumahan->id}/blok")->assertForbidden();
        $this->deleteJson("/api/admin/blok/{$blok->id}")->assertForbidden();
    }

    public function test_deleting_a_project_removes_its_blocks(): void
    {
        Blok::create(['id_perumahan' => $this->perumahan->id, 'nama' => 'Blok A']);

        $this->deleteJson("/api/admin/perumahan/{$this->perumahan->id}")->assertOk();

        $this->assertSame(0, Blok::count());
    }
}
