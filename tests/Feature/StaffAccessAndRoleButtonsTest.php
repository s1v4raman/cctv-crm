<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InstallationJob;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Staff (employees) add and edit records in every module. Admin-only: adding projects,
 * deleting records, user accounts, payroll & salaries and system settings.
 */
class StaffAccessAndRoleButtonsTest extends TestCase
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

    private function quotation(string $status = 'sent'): Quotation
    {
        $lead = Lead::create(['customer_name' => 'Ravi Stores', 'phone' => '9876500011']);

        return Quotation::create([
            'lead_id' => $lead->id, 'quotation_no' => 'QT-STAFF-' . uniqid(), 'quotation_date' => Carbon::today(),
            'valid_until' => Carbon::today()->addDays(15), 'subtotal' => 1000, 'discount' => 0,
            'tax_percent' => 18, 'tax_amount' => 180, 'total' => 1180, 'status' => $status,
        ]);
    }

    public function test_staff_add_leads_and_open_every_office_module(): void
    {
        $this->actingAs($this->staff)
            ->post(route('leads.store'), ['customer_name' => 'Staff Added Lead', 'phone' => '9876512345'])
            ->assertRedirect();
        $this->assertDatabaseHas('leads', ['customer_name' => 'Staff Added Lead']);

        foreach (['leads.create', 'jobs.create-general', 'products.create', 'suppliers.create', 'purchase-orders.create',
                  'finance.hub', 'finance.receivables.index', 'finance.payables.index', 'finance.gst.index',
                  'finance.job-costing.index', 'finance.petty_cash.index', 'finance.analytics', 'analytics.index'] as $route) {
            $this->actingAs($this->staff)->get(route($route))->assertOk();
        }
    }

    public function test_admin_only_areas_stay_closed_to_staff(): void
    {
        foreach (['projects.create', 'admin.users.index', 'finance.salaries.index', 'finance.payroll.index', 'alerts.index', 'alerts.gateways', 'wages.index'] as $route) {
            $this->actingAs($this->staff)->get(route($route))->assertForbidden();
        }
    }

    public function test_staff_cannot_delete_records(): void
    {
        $lead = Lead::create(['customer_name' => 'Keep Me', 'phone' => '9876500022']);
        $quotation = $this->quotation('draft');
        $job = InstallationJob::create(['quotation_id' => $quotation->id, 'job_no' => 'JOB-STAFF-1', 'scheduled_date' => Carbon::today(), 'status' => 'completed']);
        $invoice = Invoice::create([
            'installation_job_id' => $job->id, 'quotation_id' => $quotation->id, 'invoice_no' => 'INV-STAFF-1',
            'invoice_date' => Carbon::today(), 'due_date' => Carbon::today()->addDays(7), 'subtotal' => 1000,
            'discount' => 0, 'tax_percent' => 18, 'tax_amount' => 180, 'total' => 1180, 'amount_paid' => 0, 'status' => 'unpaid',
        ]);
        $payment = Payment::create(['invoice_id' => $invoice->id, 'amount' => 100, 'paid_on' => Carbon::today(), 'method' => 'cash']);

        $this->actingAs($this->staff)->delete(route('leads.destroy', $lead))->assertForbidden();
        $this->actingAs($this->staff)->delete(route('quotations.destroy', $quotation))->assertForbidden();
        $this->actingAs($this->staff)->delete(route('payments.destroy', $payment))->assertForbidden();

        $this->assertNotNull($lead->fresh());
        $this->assertNotNull($quotation->fresh());
        $this->assertNotNull($payment->fresh());
    }

    public function test_technicians_stay_out_of_office_pages(): void
    {
        foreach (['leads.create', 'suppliers.create', 'finance.hub', 'finance.receivables.index', 'analytics.index'] as $route) {
            $this->actingAs($this->technician)->get(route($route))->assertForbidden();
        }
    }

    public function test_staff_see_add_and_edit_buttons_but_not_delete_or_new_project(): void
    {
        $lead = Lead::create(['customer_name' => 'Button Lead', 'phone' => '9876500033']);

        $this->actingAs($this->staff)->get(route('leads.index'))
            ->assertOk()
            ->assertSee(route('leads.create'), false)
            ->assertSee(route('leads.edit', $lead), false)
            ->assertDontSee('action="' . route('leads.destroy', $lead) . '"', false);

        $this->actingAs($this->staff)->get(route('projects.index'))
            ->assertOk()
            ->assertDontSee('href="' . route('projects.create') . '"', false);

        $this->actingAs($this->admin)->get(route('projects.index'))
            ->assertSee('href="' . route('projects.create') . '"', false);
    }

    public function test_employee_hub_shows_pay_only_to_admins(): void
    {
        $this->actingAs($this->admin)->get(route('employee.hub'))
            ->assertOk()->assertSee('Monthly Burn')->assertSee('Salary by Role')->assertSee('Salary/mo');

        $this->actingAs($this->staff)->get(route('employee.hub'))
            ->assertOk()
            ->assertSee('Employee Directory')
            ->assertDontSee('Monthly Burn')
            ->assertDontSee('Salary by Role')
            ->assertDontSee('Salary/mo')
            ->assertDontSee(route('finance.payroll.index'), false);
    }

    public function test_branded_error_pages(): void
    {
        $this->actingAs($this->staff)->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertSee('Go to dashboard');

        $this->actingAs($this->staff)->get(route('projects.create'))
            ->assertForbidden()
            ->assertSee("You don't have access to this page")
            ->assertSee('Only administrators can add projects.');
    }

    public function test_dashboard_sends_real_invoiced_and_open_quote_figures(): void
    {
        $this->quotation('draft');
        $this->quotation('sent');

        $stats = $this->actingAs($this->admin)->getJson(route('dashboard.data'))->assertOk()->json('stats');

        $this->assertEquals(2360, $stats['pipeline_value']);
        $this->assertArrayHasKey('invoiced_total', $stats);
        $this->assertArrayHasKey('collected_total', $stats);
    }

    public function test_quotation_page_names_its_pdfs_and_shows_an_empty_items_row(): void
    {
        $quotation = $this->quotation('draft');

        $this->actingAs($this->staff)->get(route('quotations.show', $quotation))
            ->assertOk()
            ->assertSee('Modern PDF')
            ->assertSee('Classic PDF')
            ->assertDontSee('<span>Format 1</span>', false)
            ->assertSee('No items on this quotation yet.');
    }
}
