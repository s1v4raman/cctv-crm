<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\ServiceTicket;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The "..." menu on record lists: Mass Delete, Mass Update, Mass Email and Export.
 * Each record goes through its own single-record action, so its rules still apply.
 */
class ListActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->technician = User::factory()->create(['role' => 'technician']);
    }

    private function lead(string $name = 'Acme Stores', ?string $email = null): Lead
    {
        return Lead::create(['customer_name' => $name, 'phone' => '9876543210', 'status' => 'new', 'email' => $email]);
    }

    private function ticket(string $no, string $status = 'open'): ServiceTicket
    {
        return ServiceTicket::create([
            'ticket_no' => $no,
            'lead_id' => $this->lead()->id,
            'title' => 'Camera offline',
            'issue_type' => 'camera_offline',
            'priority' => 'medium',
            'description' => 'Gate camera shows no picture',
            'status' => $status,
        ]);
    }

    public function test_admin_mass_deletes_leads_and_staff_cannot(): void
    {
        $a = $this->lead('One');
        $b = $this->lead('Two');

        $this->actingAs($this->staff)->from('/leads')
            ->post(route('list-actions.delete', 'leads'), ['ids' => [$a->id, $b->id]])
            ->assertForbidden();
        $this->assertDatabaseCount('leads', 2);

        $this->actingAs($this->admin)->from('/leads')
            ->post(route('list-actions.delete', 'leads'), ['ids' => [$a->id, $b->id]])
            ->assertRedirect('/leads')
            ->assertSessionHas('status', '2 leads deleted.');
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_mass_delete_skips_records_their_own_delete_refuses(): void
    {
        $lead = $this->lead();
        $draft = Quotation::create(['lead_id' => $lead->id, 'quotation_no' => 'QT-1', 'status' => 'draft']);
        $sent = Quotation::create(['lead_id' => $lead->id, 'quotation_no' => 'QT-2', 'status' => 'sent']);

        $this->actingAs($this->admin)->from('/quotations')
            ->post(route('list-actions.delete', 'quotations'), ['ids' => [$draft->id, $sent->id]])
            ->assertSessionHas('status', fn ($s) => str_starts_with($s, '1 quotation deleted. 1 skipped: Only draft quotations can be deleted'));

        $this->assertDatabaseMissing('quotations', ['id' => $draft->id]);
        $this->assertDatabaseHas('quotations', ['id' => $sent->id]);
    }

    public function test_mass_delete_of_soft_deleted_sites_and_a_site_in_use(): void
    {
        $site = fn ($code) => Site::create(['site_code' => $code, 'name' => $code, 'client_name' => 'Client', 'address' => '1 Road', 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'pincode' => '600001']);
        $free = $site('SIT-1');
        $used = $site('SIT-2');
        Project::create(['project_code' => 'PRJ-1', 'title' => 'Uses site', 'project_type' => 'hardware_cctv', 'status' => 'draft', 'priority' => 'low', 'budget' => 1000, 'site_id' => $used->id]);

        $this->actingAs($this->admin)->from('/sites')
            ->post(route('list-actions.delete', 'sites'), ['ids' => [$free->id, $used->id]])
            ->assertSessionHas('status', fn ($s) => str_starts_with($s, '1 site deleted. 1 skipped: Cannot delete site because projects are already linked to it'));

        $this->assertSoftDeleted('sites', ['id' => $free->id]);
        $this->assertNotSoftDeleted('sites', ['id' => $used->id]);
    }

    public function test_mass_update_runs_the_ticket_status_action(): void
    {
        $one = $this->ticket('TCK-1');
        $two = $this->ticket('TCK-2');

        $this->actingAs($this->staff)->from('/service-tickets')
            ->post(route('list-actions.update', 'tickets'), ['ids' => [$one->id, $two->id], 'field' => 'status', 'value' => 'resolved'])
            ->assertSessionHas('status', fn ($s) => str_starts_with($s, '2 tickets updated'));

        // The ticket's own status action also stamps when it was resolved
        $this->assertSame('resolved', $one->fresh()->status);
        $this->assertNotNull($one->fresh()->resolved_at);
        $this->assertNotNull($two->fresh()->resolved_at);
    }

    public function test_technician_mass_status_change_becomes_requests_as_it_does_one_by_one(): void
    {
        $project = Project::create(['project_code' => 'PRJ-T', 'title' => 'Tech project', 'project_type' => 'hardware_cctv', 'status' => 'in_progress', 'priority' => 'low', 'budget' => 1000]);

        $this->actingAs($this->technician)->from('/projects')
            ->post(route('list-actions.update', 'projects'), ['ids' => [$project->id], 'field' => 'status', 'value' => 'completed'])
            ->assertSessionHas('status', fn ($s) => str_contains($s, '1 skipped: Status change request submitted to Administrator for review'));

        $this->assertSame('in_progress', $project->fresh()->status);
    }

    public function test_mass_update_only_takes_listed_values(): void
    {
        $ticket = $this->ticket('TCK-9');

        $this->actingAs($this->admin)->from('/service-tickets')
            ->post(route('list-actions.update', 'tickets'), ['ids' => [$ticket->id], 'field' => 'status', 'value' => 'hacked'])
            ->assertSessionHasErrors('value');
        $this->actingAs($this->admin)->from('/service-tickets')
            ->post(route('list-actions.update', 'tickets'), ['ids' => [$ticket->id], 'field' => 'title', 'value' => 'x'])
            ->assertStatus(422);

        $this->assertSame('open', $ticket->fresh()->status);
    }

    public function test_export_is_csv_for_the_office_without_passwords(): void
    {
        $response = $this->actingAs($this->admin)->get(route('list-actions.export', 'users'));
        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Email', $csv);
        $this->assertStringContainsString($this->staff->email, $csv);
        $this->assertStringNotContainsString('Password', $csv);

        $picked = $this->actingAs($this->admin)->get(route('list-actions.export', ['users', 'ids' => [$this->staff->id]]))->streamedContent();
        $this->assertStringContainsString($this->staff->email, $picked);
        $this->assertStringNotContainsString($this->technician->email, $picked);

        $this->actingAs($this->technician)->get(route('list-actions.export', 'tickets'))->assertForbidden();
    }

    public function test_mass_email_lists_addresses_for_the_office_only(): void
    {
        $with = $this->lead('With', 'owner@example.com');
        $without = $this->lead('Without');

        $this->actingAs($this->staff)
            ->postJson(route('list-actions.emails', 'leads'), ['ids' => [$with->id, $without->id]])
            ->assertOk()
            ->assertExactJson(['emails' => ['owner@example.com'], 'missing' => 1]);

        $this->actingAs($this->technician)
            ->postJson(route('list-actions.emails', 'leads'), ['ids' => [$with->id]])
            ->assertForbidden();
    }

    public function test_workers_and_customers_cannot_use_list_actions(): void
    {
        $lead = $this->lead();
        foreach (['worker', 'customer'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->postJson(route('list-actions.delete', 'leads'), ['ids' => [$lead->id]])->assertForbidden();
        }
        $this->assertDatabaseHas('leads', ['id' => $lead->id]);
    }

    public function test_the_menu_offers_what_each_role_may_do(): void
    {
        $this->ticket('TCK-M');

        $this->actingAs($this->admin)->get(route('service-tickets.index'))
            ->assertOk()->assertSee('Mass Delete')->assertSee('Mass Update')->assertSee('Mass Email')->assertSee('Print View')
            ->assertSee('aria-label="Select TCK-M"', false);

        $this->actingAs($this->staff)->get(route('service-tickets.index'))
            ->assertOk()->assertDontSee('Mass Delete')->assertSee('Mass Update')->assertSee('Mass Email');

        // Technicians change tickets one by one too, but don't export or email whole lists
        $this->actingAs($this->technician)->get(route('service-tickets.index'))
            ->assertOk()->assertDontSee('Mass Delete')->assertSee('Mass Update')->assertDontSee('Mass Email');
    }
}
