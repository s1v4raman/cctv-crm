<?php

namespace Tests\Feature;

use App\Models\EmployeeSalary;
use App\Models\ExpenseClaim;
use App\Models\InstallationJob;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobCostingAndProjectProfitLossTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin User']);
        $this->staff = User::factory()->create(['role' => 'staff', 'name' => 'Staff Member']);
        $this->technician = User::factory()->create(['role' => 'technician', 'name' => 'Kannan Tech']);

        // Set technician salary structure (₹250/hr)
        EmployeeSalary::create([
            'user_id'             => $this->technician->id,
            'base_salary_monthly' => 35000.00,
            'daily_rate'          => 1346.15,
            'hourly_rate'         => 250.00,
            'payment_method'      => 'bank_transfer',
        ]);
    }

    public function test_non_admin_is_forbidden_from_job_costing(): void
    {
        $this->actingAs($this->staff)
            ->get(route('finance.job-costing.index'))
            ->assertForbidden();

        $this->actingAs($this->technician)
            ->get(route('finance.job-costing.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_job_costing_hub_and_view_metrics(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Grand Palace Hotel',
            'phone'         => '9876543210',
            'site_address'  => 'OMR, Chennai',
        ]);

        $camera = Product::create([
            'name'       => '8MP 4K Bullet Camera',
            'sku'        => 'CAM-4K-01',
            'cost_price' => 3000.00,
            'unit_price' => 6000.00,
        ]);

        $quotation = Quotation::create([
            'lead_id'      => $lead->id,
            'quotation_no' => 'QT-HOTEL-01',
            'subtotal'     => 24000.00,
            'discount'     => 0.00,
            'tax_percent'  => 18.00,
            'tax_amount'   => 4320.00,
            'total'        => 28320.00,
            'status'       => 'accepted',
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id'   => $camera->id,
            'item_name'    => '8MP 4K Bullet Camera',
            'quantity'     => 4,
            'unit_price'   => 6000.00,
            'total'        => 24000.00,
        ]);

        $job = InstallationJob::create([
            'quotation_id'           => $quotation->id,
            'job_no'                 => 'JOB-HOTEL-01',
            'assigned_technician_id' => $this->technician->id,
            'status'                 => 'completed',
            'scheduled_date'         => now()->toDateString(),
            'labor_hours_logged'     => 8.0,
            'other_direct_costs'     => 1000.00,
        ]);

        // Invoice for Job
        Invoice::create([
            'installation_job_id' => $job->id,
            'quotation_id'        => $quotation->id,
            'invoice_no'          => 'INV-HOTEL-01',
            'invoice_date'        => now()->toDateString(),
            'subtotal'            => 24000.00,
            'discount'            => 0.00,
            'tax_percent'         => 18.00,
            'tax_amount'          => 4320.00,
            'total'               => 28320.00,
            'amount_paid'         => 28320.00,
            'status'              => 'paid',
        ]);

        // Link an approved travel expense claim to this job
        ExpenseClaim::create([
            'claim_no'            => 'EXP-HOTEL-01',
            'user_id'             => $this->technician->id,
            'installation_job_id' => $job->id,
            'expense_category'    => 'fuel_travel',
            'expense_date'        => now()->toDateString(),
            'amount'              => 500.00,
            'status'              => 'approved',
            'description'         => 'Fuel travel to OMR site',
        ]);

        // Access Index
        $response = $this->actingAs($this->admin)->get(route('finance.job-costing.index'));
        $response->assertOk();
        $response->assertSee('Job Costing & Per-Project Profit & Loss', false);
        $response->assertSee('JOB-HOTEL-01');
        $response->assertSee('Grand Palace Hotel');
        $response->assertSee('24,000.00'); // Revenue
        $response->assertSee('12,000.00'); // Hardware COGS (4 * 3000)

        // Access Show Breakdown
        $showResponse = $this->actingAs($this->admin)->get(route('finance.job-costing.show', $job->id));
        $showResponse->assertOk();
        $showResponse->assertSee('JOB-HOTEL-01');
        $showResponse->assertSee('CAM-4K-01');
        $showResponse->assertSee('8MP 4K Bullet Camera');
        $showResponse->assertSee('2,000.00'); // Labor cost: 8h * 250
        $showResponse->assertSee('500.00');   // Field expenses
        $showResponse->assertSee('1,000.00'); // Other direct costs
        // Net Profit = 24000 - 12000 (hardware) - 2000 (labor) - 500 (travel) - 1000 (other) = 8500
        $showResponse->assertSee('8,500.00'); // Net Profit
    }

    public function test_updating_labor_hours_and_overheads_recalculates_margins(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Metro Supermarket',
            'phone'         => '9876500000',
        ]);

        $quotation = Quotation::create([
            'lead_id'      => $lead->id,
            'quotation_no' => 'QT-METRO-01',
            'subtotal'     => 10000.00,
            'discount'     => 0.00,
            'tax_percent'  => 18.00,
            'tax_amount'   => 1800.00,
            'total'        => 11800.00,
            'status'       => 'accepted',
        ]);

        $job = InstallationJob::create([
            'quotation_id'           => $quotation->id,
            'job_no'                 => 'JOB-METRO-01',
            'assigned_technician_id' => $this->technician->id,
            'status'                 => 'completed',
            'scheduled_date'         => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('finance.job-costing.update', $job->id), [
            'labor_hours_logged' => 10.0,
            'custom_hourly_rate' => 300.00,
            'other_direct_costs' => 500.00,
            'costing_notes'      => 'Rental of tall step ladder and conduit piping',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('installation_jobs', [
            'id'                 => $job->id,
            'labor_hours_logged' => 10.0,
            'custom_hourly_rate' => 300.00,
            'other_direct_costs' => 500.00,
        ]);
    }

    public function test_export_endpoints(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Delta Tech Labs',
            'phone'         => '9123456789',
        ]);

        $quotation = Quotation::create([
            'lead_id'      => $lead->id,
            'quotation_no' => 'QT-DELTA-01',
            'subtotal'     => 15000.00,
            'discount'     => 0.00,
            'tax_percent'  => 18.00,
            'tax_amount'   => 2700.00,
            'total'        => 17700.00,
            'status'       => 'accepted',
        ]);

        $job = InstallationJob::create([
            'quotation_id'           => $quotation->id,
            'job_no'                 => 'JOB-DELTA-01',
            'assigned_technician_id' => $this->technician->id,
            'status'                 => 'completed',
            'scheduled_date'         => now()->toDateString(),
        ]);

        // 1. PDF Export
        $pdfResp = $this->actingAs($this->admin)->get(route('finance.job-costing.pdf', $job->id));
        $pdfResp->assertOk();
        $pdfResp->assertHeader('Content-Type', 'application/pdf');

        // 2. CSV Portfolio Export
        $csvResp = $this->actingAs($this->admin)->get(route('finance.job-costing.export-csv'));
        $csvResp->assertOk();
        $this->assertStringContainsString('CCTV PROJECT COSTING & PROFITABILITY LEDGER', $csvResp->getContent());
        $this->assertStringContainsString('JOB-DELTA-01', $csvResp->getContent());
    }
}
