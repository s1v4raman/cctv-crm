<?php

namespace Tests\Feature;

use App\Models\AmcContract;
use App\Models\InstallationJob;
use App\Models\JobCompletionReport;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\ServiceTicket;
use App\Models\User;
use App\Services\ExecutiveAnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedReportingAndAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->technician = User::factory()->create([
            'name' => 'John FieldTech',
            'email' => 'tech@cctvcrm.com',
            'role' => 'technician',
        ]);
    }

    public function test_technician_performance_metrics_calculation(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Acme Bank',
            'phone' => '9876543210',
            'email' => 'acme@bank.com',
            'status' => 'won',
        ]);

        $quotation = Quotation::create([
            'lead_id' => $lead->id,
            'quotation_no' => 'QTN-20260901-001',
            'quotation_date' => Carbon::now()->subDays(6),
            'valid_until' => Carbon::now()->addDays(20),
            'subtotal' => 20000,
            'total' => 23600,
            'status' => 'accepted',
        ]);

        // 1. Completed Installation Job
        InstallationJob::create([
            'quotation_id' => $quotation->id,
            'assigned_technician_id' => $this->technician->id,
            'job_no' => 'JOB-20260901-001',
            'status' => 'completed',
            'scheduled_date' => Carbon::now()->subDays(5),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        // 2. Service Ticket (First-Time Fix)
        $ticket1 = ServiceTicket::create([
            'ticket_no' => 'TCK-20260901-001',
            'lead_id' => $lead->id,
            'assigned_technician_id' => $this->technician->id,
            'title' => 'Camera Feed Blurry',
            'description' => 'Camera feed blurry due to dirty lens',
            'issue_type' => 'blurry_feed',
            'priority' => 'medium',
            'status' => 'resolved',
        ]);

        ServiceTicket::where('id', $ticket1->id)->update([
            'created_at' => Carbon::now()->subDays(4),
            'resolved_at' => Carbon::now()->subDays(4)->addHours(3),
            'updated_at' => Carbon::now()->subDays(4)->addHours(3),
        ]);

        // 3. JCR Report
        JobCompletionReport::create([
            'report_no' => 'JCR-20260901-001',
            'service_ticket_id' => $ticket1->id,
            'lead_id' => $lead->id,
            'technician_id' => $this->technician->id,
            'completion_date' => Carbon::now()->subDays(4)->format('Y-m-d'),
            'signer_name' => 'Acme Manager',
            'customer_rating' => 5,
            'customer_signature' => 'data:image/png;base64,sample',
            'work_summary' => 'Lens cleaned successfully',
            'created_at' => Carbon::now()->subDays(4),
        ]);

        $analyticsService = app(ExecutiveAnalyticsService::class);
        $result = $analyticsService->getTechnicianPerformanceDetails(Carbon::now()->subDays(10)->startOfDay(), Carbon::now()->endOfDay());

        $this->assertNotEmpty($result['technicians']);
        $techMetrics = collect($result['technicians'])->firstWhere('technician_id', $this->technician->id);

        $this->assertNotNull($techMetrics);
        $this->assertEquals(1, $techMetrics['jobs_completed']);
        $this->assertEquals(1, $techMetrics['tickets_resolved']);
        $this->assertEquals(100.0, $techMetrics['first_time_fix_rate']);
        $this->assertEquals(3.0, $techMetrics['avg_resolution_hours']);
        $this->assertEquals(5.0, $techMetrics['average_rating']);
        $this->assertEquals(1, $techMetrics['jcr_signoffs']);
    }

    public function test_mrr_and_amc_retention_metrics_calculation(): void
    {
        $lead = Lead::create([
            'customer_name' => 'Metro Retail',
            'phone' => '9876543211',
            'email' => 'metro@retail.com',
            'status' => 'won',
        ]);

        // Active Annual AMC: Value 120,000 -> MRR 10,000
        AmcContract::create([
            'lead_id' => $lead->id,
            'contract_no' => 'AMC-2026-001',
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->addMonths(10),
            'value' => 120000,
            'frequency' => 'annually',
            'status' => 'active',
        ]);

        // Active Monthly AMC: Value 5,000 -> MRR 5,000
        AmcContract::create([
            'lead_id' => $lead->id,
            'contract_no' => 'AMC-2026-002',
            'start_date' => Carbon::now()->subMonths(1),
            'end_date' => Carbon::now()->addMonths(11),
            'value' => 5000,
            'frequency' => 'monthly',
            'status' => 'active',
        ]);

        // Expired AMC
        AmcContract::create([
            'lead_id' => $lead->id,
            'contract_no' => 'AMC-2025-001',
            'start_date' => Carbon::now()->subMonths(14),
            'end_date' => Carbon::now()->subMonths(2),
            'value' => 30000,
            'frequency' => 'annually',
            'status' => 'expired',
        ]);

        $analyticsService = app(ExecutiveAnalyticsService::class);
        $result = $analyticsService->getMrrAndRetentionMetrics(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());

        $this->assertEquals(2, $result['snapshot']['active_contracts']);
        $this->assertEquals(15000.0, $result['snapshot']['active_mrr']);
        $this->assertEquals(180000.0, $result['snapshot']['active_arr']);
        $this->assertEquals(1, $result['snapshot']['expired_contracts']);
        // Retention: 2 / (2 + 1) = 66.7%
        $this->assertEquals(66.7, $result['snapshot']['retention_rate']);
        $this->assertEquals(33.3, $result['snapshot']['churn_rate']);
    }

    public function test_lead_to_quotation_conversion_rate_calculation(): void
    {
        $lead1 = Lead::create([
            'customer_name' => 'Tech Corp',
            'phone' => '9876543212',
            'email' => 'tech@corp.com',
            'status' => 'won',
            'created_at' => Carbon::now()->subDays(10),
        ]);

        $lead2 = Lead::create([
            'customer_name' => 'Delta Logistics',
            'phone' => '9876543213',
            'email' => 'delta@logistics.com',
            'status' => 'quoted',
            'created_at' => Carbon::now()->subDays(8),
        ]);

        // Quotation 1: Accepted
        Quotation::create([
            'lead_id' => $lead1->id,
            'quotation_no' => 'QTN-202609-001',
            'quotation_date' => Carbon::now()->subDays(7),
            'valid_until' => Carbon::now()->addDays(20),
            'subtotal' => 50000,
            'tax_percent' => 18,
            'tax_amount' => 9000,
            'total' => 59000,
            'status' => 'accepted',
            'accepted_at' => Carbon::now()->subDays(5),
            'created_at' => Carbon::now()->subDays(7),
        ]);

        // Quotation 2: Sent/Pending
        Quotation::create([
            'lead_id' => $lead2->id,
            'quotation_no' => 'QTN-202609-002',
            'quotation_date' => Carbon::now()->subDays(3),
            'valid_until' => Carbon::now()->addDays(25),
            'subtotal' => 30000,
            'tax_percent' => 18,
            'tax_amount' => 5400,
            'total' => 35400,
            'status' => 'sent',
            'created_at' => Carbon::now()->subDays(3),
        ]);

        $analyticsService = app(ExecutiveAnalyticsService::class);
        $result = $analyticsService->getMrrAndRetentionMetrics(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());

        $this->assertEquals(2, $result['conversion']['range_leads']);
        $this->assertEquals(2, $result['conversion']['range_quotes']);
        $this->assertEquals(1, $result['conversion']['range_accepted_quotes']);
        $this->assertEquals(100.0, $result['conversion']['lead_to_quote_rate']);
        $this->assertEquals(50.0, $result['conversion']['quote_to_accepted_rate']);
        $this->assertEquals(50.0, $result['conversion']['lead_to_won_rate']);
        $this->assertEquals(59000.0, $result['conversion']['range_accepted_total']);
    }

    public function test_technician_analytics_view_renders_correctly(): void
    {
        $response = $this->actingAs($this->admin)->get(route('analytics.technicians'));

        $response->assertStatus(200);
        $response->assertSee('Technician Performance');
        $response->assertSee('First-Time Fix Rate (FTFR)');
        $response->assertSee('Avg Resolution Time (MTTR)');
        $response->assertSee('Completed Installations');
        $response->assertSee('Export Technician Scorecard PDF');
    }

    public function test_mrr_retention_view_renders_correctly(): void
    {
        $response = $this->actingAs($this->admin)->get(route('analytics.mrr-retention'));

        $response->assertStatus(200);
        $response->assertSee('Monthly Revenue, AMC Retention');
        $response->assertSee('Monthly Recurring (MRR)');
        $response->assertSee('AMC Contract Retention');
        $response->assertSee('Lead-to-Won Conversion');
        $response->assertSee('Export MRR');
    }

    public function test_technician_pdf_export_streams_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get(route('analytics.technicians.export-pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_mrr_retention_pdf_export_streams_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get(route('analytics.mrr-retention.export-pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_analytics_api_endpoints_return_json(): void
    {
        $techApiResponse = $this->actingAs($this->admin)->get(route('analytics.api.technicians'));
        $techApiResponse->assertStatus(200);
        $techApiResponse->assertJsonStructure([
            'date_range',
            'technicians',
            'summary' => [
                'team_avg_ftfr',
                'team_avg_resolution_hours',
                'total_jobs_completed',
                'total_tickets_resolved',
            ],
            'chart_data',
        ]);

        $mrrApiResponse = $this->actingAs($this->admin)->get(route('analytics.api.mrr-retention'));
        $mrrApiResponse->assertStatus(200);
        $mrrApiResponse->assertJsonStructure([
            'date_range',
            'snapshot' => [
                'active_contracts',
                'active_arr',
                'active_mrr',
                'retention_rate',
            ],
            'timeline',
            'conversion',
        ]);
    }
}
