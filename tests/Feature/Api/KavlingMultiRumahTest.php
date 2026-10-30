<?php

namespace Tests\Feature\Api;

use App\Models\Akun;
use App\Models\Kavling;
use App\Models\Leads;
use App\Models\Perumahan;
use App\Models\TipeRumah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KavlingMultiRumahTest extends TestCase
{
    use RefreshDatabase;

    private TipeRumah $tipe;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(Akun::factory()->admin()->create());
        $this->tipe = TipeRumah::factory()->create();
    }

    private function add(array $data, ?TipeRumah $tipe = null)
    {
        return $this->postJson('/api/admin/tipe-rumah/'.($tipe ?? $this->tipe)->id.'/kavling', $data);
    }

    public function test_single_house_kavling_stays_unnumbered(): void
    {
        $this->add(['blok' => 'Blok A', 'nomor_kavling' => 'A-01'])
            ->assertCreated()
            ->assertJsonPath('data.nomor_rumah', null)
            ->assertJsonPath('data.label', 'A-01')
            ->assertJsonPath('data.jumlah_dibuat', 1);

        $this->assertSame(1, Kavling::count());
    }

    public function test_one_kavling_can_be_created_with_several_houses(): void
    {
        $this->add(['blok' => 'Blok A', 'nomor_kavling' => 'A-01', 'jumlah_rumah' => 3])
            ->assertCreated()
            ->assertJsonPath('data.jumlah_dibuat', 3)
            ->assertJsonPath('data.nomor_rumah', '1')
            ->assertJsonPath('data.label', 'A-01 · Rumah 1');

        $this->assertSame(['1', '2', '3'], Kavling::orderBy('id')->pluck('nomor_rumah')->all());
        $this->assertSame(3, Kavling::where('nomor_kavling', 'A-01')->where('blok', 'Blok A')->count());
    }

    public function test_adding_houses_to_an_existing_kavling_continues_numbering(): void
    {
        $this->add(['blok' => 'Blok A', 'nomor_kavling' => 'A-01', 'jumlah_rumah' => 2])->assertCreated();
        $this->add(['blok' => 'Blok A', 'nomor_kavling' => 'A-01', 'jumlah_rumah' => 2])->assertCreated();

        $this->assertSame(['1', '2', '3', '4'], Kavling::orderBy('id')->pluck('nomor_rumah')->all());
    }

    public function test_adding_a_second_house_numbers_the_existing_unnumbered_one(): void
    {
        $this->add(['blok' => 'Blok A', 'nomor_kavling' => 'A-01'])->assertCreated();
        $this->add(['blok' => 'Blok A', 'nomor_kavling' => 'A-01'])
            ->assertCreated()
            ->assertJsonPath('data.nomor_rumah', '2');

        $this->assertSame(['1', '2'], Kavling::orderBy('id')->pluck('nomor_rumah')->all());
    }

    public function test_same_number_in_another_block_or_project_is_a_different_kavling(): void
    {
        $this->add(['blok' => 'Blok A', 'nomor_kavling' => '01'])->assertCreated();
        $this->add(['blok' => 'Blok B', 'nomor_kavling' => '01'])->assertCreated();
        $this->add(['nomor_kavling' => '01'])->assertCreated();
        $otherProject = TipeRumah::factory()->create(['id_perumahan' => Perumahan::factory()->create()->id]);
        $this->add(['blok' => 'Blok A', 'nomor_kavling' => '01'], $otherProject)->assertCreated();

        $this->assertSame([null, null, null, null], Kavling::pluck('nomor_rumah')->all());
    }

    public function test_houses_of_other_type_can_share_the_kavling(): void
    {
        $other = TipeRumah::factory()->create(['id_perumahan' => $this->tipe->id_perumahan]);

        $this->add(['blok' => 'Blok A', 'nomor_kavling' => 'A-01'])->assertCreated();
        $this->add(['blok' => 'Blok A', 'nomor_kavling' => 'A-01'], $other)->assertCreated()->assertJsonPath('data.nomor_rumah', '2');

        $this->assertSame(2, Kavling::where('nomor_kavling', 'A-01')->count());
        $this->assertSame([$this->tipe->id, $other->id], Kavling::orderBy('id')->pluck('id_tipe')->all());
    }

    public function test_house_count_is_validated(): void
    {
        $this->add(['nomor_kavling' => 'A-01', 'jumlah_rumah' => 0])->assertStatus(422)->assertJsonStructure(['data' => ['jumlah_rumah']]);
        $this->add(['nomor_kavling' => 'A-01', 'jumlah_rumah' => 21])->assertStatus(422);
        $this->add(['nomor_kavling' => 'A-01', 'jumlah_rumah' => 'abc'])->assertStatus(422);

        $this->assertSame(0, Kavling::count());
    }

    public function test_each_house_has_its_own_status_and_buyer(): void
    {
        $this->add(['blok' => 'Blok A', 'nomor_kavling' => 'A-01', 'jumlah_rumah' => 2])->assertCreated();
        [$first, $second] = Kavling::orderBy('id')->get()->all();
        $lead = Leads::factory()->create(['nama_calon' => 'Ahmad Rizky']);

        $this->putJson("/api/admin/kavling/{$first->id}", ['status' => 'terjual', 'id_leads' => $lead->id])->assertOk();

        $this->assertSame('terjual', $first->fresh()->status->value);
        $this->assertSame($lead->id, $first->fresh()->id_leads);
        $this->assertSame('tersedia', $second->fresh()->status->value);
        $this->assertNull($second->fresh()->id_leads);

        $this->getJson('/api/admin/kavling?id_perumahan='.$this->tipe->id_perumahan)
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.label', 'A-01 · Rumah 1')
            ->assertJsonPath('data.1.label', 'A-01 · Rumah 2');
    }

    public function test_house_numbers_are_counted_as_units_in_perumahan_stats(): void
    {
        $this->add(['blok' => 'Blok A', 'nomor_kavling' => 'A-01', 'jumlah_rumah' => 3])->assertCreated();
        Kavling::first()->update(['status' => 'booking']);

        $this->getJson('/api/admin/perumahan')
            ->assertOk()
            ->assertJsonPath('data.0.unit.total', 3)
            ->assertJsonPath('data.0.unit.tersedia', 2)
            ->assertJsonPath('data.0.unit.booking', 1);
    }

    public function test_renumbering_a_house_cannot_collide_with_a_sibling(): void
    {
        $this->add(['blok' => 'Blok A', 'nomor_kavling' => 'A-01', 'jumlah_rumah' => 2])->assertCreated();
        [$first, $second] = Kavling::orderBy('id')->get()->all();

        $this->putJson("/api/admin/kavling/{$second->id}", ['status' => 'tersedia', 'nomor_rumah' => '1'])
            ->assertStatus(422)->assertJsonStructure(['data' => ['nomor_rumah']]);
        $this->putJson("/api/admin/kavling/{$second->id}", ['status' => 'tersedia', 'nomor_rumah' => 'B'])
            ->assertOk()->assertJsonPath('data.label', 'A-01 · Rumah B');

        $this->assertSame('B', $second->fresh()->nomor_rumah);
        $this->assertSame('1', $first->fresh()->nomor_rumah);
    }

    public function test_plain_status_updates_skip_the_collision_check(): void
    {
        // legacy data: two unnumbered houses with the same kavling number
        $a = Kavling::factory()->create(['id_tipe' => $this->tipe->id, 'blok' => 'Blok A', 'nomor_kavling' => 'A-01']);
        Kavling::factory()->create(['id_tipe' => $this->tipe->id, 'blok' => 'Blok A', 'nomor_kavling' => 'A-01']);

        $this->putJson("/api/admin/kavling/{$a->id}", ['status' => 'booking'])->assertOk();
    }

    public function test_public_tipe_detail_lists_each_house_with_its_label(): void
    {
        $this->add(['blok' => 'Blok A', 'nomor_kavling' => 'A-01', 'jumlah_rumah' => 2])->assertCreated();

        $this->getJson("/api/public/tipe-rumah/{$this->tipe->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data.kavling')
            ->assertJsonPath('data.kavling.1.label', 'A-01 · Rumah 2');
    }
}
