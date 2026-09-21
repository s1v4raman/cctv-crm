<?php

namespace Tests\Feature;

use App\Models\AmcContract;
use App\Models\InstallationJob;
use App\Models\Invoice;
use App\Models\JobCompletionReport;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\ServiceTicket;
use App\Models\User;
use App\Services\ExecutiveAnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutiveAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $technician;
    private Lead $lead;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Boss']);
        $this->technician = User::factory()->create(['role' => 'technician', 'name' => 'Rohan FieldTech']);

        $this->lead = Lead::create([
            'customer_name' => 'Fortis Healthcare Hospital',
            'email'         => 'procurement@fortis.in',
            'phone'         => '9876543210',
            'site_address'  => 'Fortis Hospital, Sector 62, Mohali',
            'status'        => 'won',
        ]);

        $this->product = Product::create([
            'name'           => 'Hikvision 4K 8MP Darkfighter Camera',
            'sku'            => 'DS-2CD2185FWD-I',
            'model_no'       => 'DS-2CD2185FWD-I',
            'unit_price'     => 10000,
            'cost_price'     => 6000, // 40% margin
            'stock_quantity' => 20,
            'is_active'      => true,
        ]);
    }

    public function test_user_can_view_executive_analytics_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('analytics.index'));
        $response->assertOk();
        $response->assertSee('Executive Business Analytics');
        $response->assertSee('Total Revenue');
        $response->assertSee('COGS');
        $response->assertSee('Gross Profit');
        $response->assertSee('Field Engineering');
    }

    public function test_financial_profitability_and_margin_calculation(): void
    {
        // 1. Create quote with items
        $quote = Quotation::create([
            'lead_id'        => $this->lead->id,
            'quotation_no'   => 'QT-2026-0099',
            'quotation_date' => Carbon::today(),
            'subtotal'       => 50000,
            'tax_amount'     => 9000,
            'total'          => 59000,
            'status'         => 'accepted',
        ]);

        QuotationItem::create([
            'quotation_id' => $quote->id,
            'product_id'   => $this->product->id,
            'item_name'    => $this->product->name,
            'quantity'     => 5,
            'unit_price'   => 10000,
            'total_price'  => 50000,
        ]);

        // 2. Create installation job & invoice
        $job = InstallationJob::create([
            'quotation_id'            => $quote->id,
            'job_no'                  => 'JOB-2026-0099',
            'status'                  => 'completed',
            'assigned_technician_id'  => $this->technician->id,
            'scheduled_date'          => Carbon::today(),
        ]);

        Invoice::create([
            'installation_job_id' => $job->id,
            'quotation_id'        => $quote->id,
            'invoice_no'          => 'INV-2026-0099',
            'invoice_date'        => Carbon::today(),
            'due_date'            => Carbon::today()->addDays(15),
            'subtotal'            => 50000,
            'tax_amount'          => 9000,
            'total'               => 59000,
            'status'              => 'unpaid',
        ]);

        $service = app(ExecutiveAnalyticsService::class);
        $metrics = $service->getFinancialMetrics(Carbon::today()->startOfMonth(), Carbon::today()->endOfMonth());

        // Invoiced Revenue should be 59,000
        $this->assertEquals(59000.0, $metrics['invoiced_revenue']);

        // COGS for 5 cameras @ 6,000 = 30,000
        $this->assertEquals(30000.0, $metrics['cogs']);

        // Gross Profit = 59000 - 30000 = 29,000
        $this->assertEquals(29000.0, $metrics['gross_profit']);
        $this->assertEquals(49.2, $metrics['gross_margin_percent']);
    }

    public function test_technician_scorecard_aggregates_jobs_and_csat(): void
    {
        // 1. Create completed job for technician
        $quote = Quotation::create([
            'lead_id'        => $this->lead->id,
            'quotation_no'   => 'QT-2026-0100',
            'quotation_date' => Carbon::today(),
            'subtotal'       => 20000,
            'tax_amount'     => 3600,
            'total'          => 23600,
            'status'         => 'accepted',
        ]);

        $job = InstallationJob::create([
            'quotation_id'           => $quote->id,
            'job_no'                 => 'JOB-2026-0100',
            'status'                 => 'completed',
            'assigned_technician_id' => $this->technician->id,
            'scheduled_date'         => Carbon::today(),
        ]);

        // 2. Create signed JCR report with 5-star rating
        JobCompletionReport::create([
            'report_no'              => 'JCR-2026-0099',
            'installation_job_id'    => $job->id,
            'lead_id'                => $this->lead->id,
            'technician_id'          => $this->technician->id,
            'completion_date'        => Carbon::today(),
            'signer_name'            => 'Dr. Gupta',
            'signer_designation'     => 'Director',
            'signer_phone'           => '9876543210',
            'customer_signature'     => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'customer_rating'        => 5,
            'checklist_data'         => [],
            'signed_at'              => now(),
        ]);

        $service = app(ExecutiveAnalyticsService::class);
        $scorecard = $service->getTechnicianScorecard(Carbon::today()->startOfMonth(), Carbon::today()->endOfMonth());

        $techRow = collect($scorecard)->firstWhere('technician_id', $this->technician->id);
        $this->assertNotNull($techRow);
        $this->assertEquals(1, $techRow['jobs_completed']);
        $this->assertEquals(1, $techRow['jcr_signoffs']);
        $this->assertEquals(5.0, $techRow['average_rating']);
    }

    public function test_export_executive_pdf_report(): void
    {
        $response = $this->actingAs($this->admin)->get(route('analytics.export-pdf', ['range' => 'this_month']));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_api_data_returns_json_payload(): void
    {
        $response = $this->actingAs($this->admin)->get(route('analytics.api', ['range' => 'this_month']));
        $response->assertOk();
        $response->assertJsonStructure([
            'date_range',
            'financials' => ['invoiced_revenue', 'cogs', 'gross_profit', 'gross_margin_percent', 'cash_collected', 'total_receivables'],
            'amc'        => ['active_contracts', 'arr', 'mrr'],
            'monthly_trend' => ['labels', 'revenue', 'cogs', 'profit'],
            'lead_funnel',
            'technicians',
            'top_products',
        ]);
    }
}
