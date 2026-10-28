<?php

namespace Tests\Feature;

use App\Filament\Auth\Login;
use App\Filament\Resources\Kavlings\Pages\EditKavling;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Models\Akun;
use App\Models\Kalender;
use App\Models\Kavling;
use App\Models\Leads;
use App\Models\ReportMarketing;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private Akun $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Akun::factory()->admin()->create(['username' => 'admin']);
        Filament::setCurrentPanel('admin');
    }

    public function test_guest_is_redirected_to_panel_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertSee('Username');
    }

    public function test_admin_can_log_in_with_username(): void
    {
        Livewire::test(Login::class)
            ->set('data.username', 'admin')
            ->set('data.password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($this->admin, 'admin');
    }

    public function test_wrong_password_shows_error_on_username(): void
    {
        Livewire::test(Login::class)
            ->set('data.username', 'admin')
            ->set('data.password', 'salah')
            ->call('authenticate')
            ->assertHasErrors(['data.username']);
    }

    public function test_marketing_account_cannot_open_panel(): void
    {
        $this->actingAs(Akun::factory()->marketing()->create(), 'admin')
            ->get('/admin')
            ->assertForbidden();
    }

    #[DataProvider('panelPages')]
    public function test_admin_can_open_panel_pages(string $uri): void
    {
        $lead = Leads::factory()->withKtp()->create();
        Kavling::factory()->create(['status' => 'booking', 'id_leads' => $lead->id]);
        ReportMarketing::factory()->create(['id_leads' => $lead->id]);
        Kalender::factory()->create();

        $this->actingAs($this->admin, 'admin')
            ->get(str_replace('{lead}', (string) $lead->id, $uri))
            ->assertOk();
    }

    public static function panelPages(): array
    {
        return [
            'dashboard' => ['/admin'],
            'perumahan' => ['/admin/perumahan'],
            'perumahan create' => ['/admin/perumahan/create'],
            'kavling' => ['/admin/kavling'],
            'kavling create' => ['/admin/kavling/create'],
            'leads' => ['/admin/leads'],
            'lead edit' => ['/admin/leads/{lead}/edit'],
            'marketing' => ['/admin/marketing'],
            'marketing create' => ['/admin/marketing/create'],
            'reports' => ['/admin/reports'],
            'agenda' => ['/admin/agenda'],
        ];
    }

    public function test_leads_have_no_create_page(): void
    {
        $this->actingAs($this->admin, 'admin')->get('/admin/leads/create')->assertNotFound();
    }

    public function test_validate_action_creates_portal_account(): void
    {
        $this->actingAs($this->admin, 'admin');
        $marketing = Akun::factory()->marketing()->create();
        $lead = Leads::factory()->withKtp()->create(['no_telp' => '081234567890']);

        Livewire::test(ListLeads::class)
            ->callAction(TestAction::make('validasi')->table($lead), data: ['id_marketing' => $marketing->id])
            ->assertHasNoFormErrors()
            ->assertNotified('Lead tervalidasi');

        $lead->refresh();
        $this->assertSame('validated', $lead->status_keseriusan->value);
        $this->assertSame($marketing->id, $lead->id_marketing);
        $this->assertDatabaseHas('tb_akun', ['username' => '081234567890', 'role' => 'leads']);
    }

    public function test_assign_action_hands_pending_lead_to_marketing(): void
    {
        $this->actingAs($this->admin, 'admin');
        $marketing = Akun::factory()->marketing()->create();
        $lead = Leads::factory()->create();

        Livewire::test(ListLeads::class)
            ->callAction(TestAction::make('tugaskan')->table($lead), data: ['id_marketing' => $marketing->id])
            ->assertHasNoFormErrors()
            ->assertNotified('Lead ditugaskan ke marketing');

        $lead->refresh();
        $this->assertSame($marketing->id, $lead->id_marketing);
        $this->assertSame('pending', $lead->status_keseriusan->value);
    }

    public function test_assign_action_is_hidden_once_validated(): void
    {
        $this->actingAs($this->admin, 'admin');
        $lead = Leads::factory()->create(['status_keseriusan' => 'validated']);

        Livewire::test(ListLeads::class)
            ->assertActionHidden(TestAction::make('tugaskan')->table($lead));
    }

    public function test_validate_action_without_ktp_is_refused(): void
    {
        $this->actingAs($this->admin, 'admin');
        $marketing = Akun::factory()->marketing()->create();
        $lead = Leads::factory()->create();

        Livewire::test(ListLeads::class)
            ->callAction(TestAction::make('validasi')->table($lead), data: ['id_marketing' => $marketing->id])
            ->assertNotified('Validasi gagal');

        $this->assertSame('pending', $lead->fresh()->status_keseriusan->value);
    }

    public function test_reject_action(): void
    {
        $this->actingAs($this->admin, 'admin');
        $lead = Leads::factory()->create();

        Livewire::test(ListLeads::class)
            ->callAction(TestAction::make('tolak')->table($lead));

        $this->assertSame('rejected', $lead->fresh()->status_keseriusan->value);
    }

    public function test_setting_kavling_available_releases_buyer(): void
    {
        $this->actingAs($this->admin, 'admin');
        $lead = Leads::factory()->create();
        $kavling = Kavling::factory()->create(['status' => 'booking', 'id_leads' => $lead->id]);

        Livewire::test(EditKavling::class, ['record' => $kavling->getRouteKey()])
            ->fillForm(['status' => 'tersedia'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull($kavling->fresh()->id_leads);
    }
}
