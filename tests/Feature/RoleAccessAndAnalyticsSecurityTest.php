<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\InstallationJob;
use App\Models\ServiceTicket;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessAndAnalyticsSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'  => 'Admin User',
            'email' => 'admin@securevision.test',
            'role'  => 'admin',
        ]);

        $this->staff = User::factory()->create([
            'name'  => 'Staff Employee',
            'email' => 'employee@securevision.test',
            'role'  => 'staff',
        ]);

        $this->technician = User::factory()->create([
            'name'  => 'Tech Lead',
            'email' => 'technician@securevision.test',
            'role'  => 'technician',
        ]);
    }

    public function test_admin_can_access_all_executive_analytics_and_finance_routes(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('analytics.index'))->assertOk();
        $this->get(route('analytics.cost-profit'))->assertOk();
        $this->get(route('analytics.technicians'))->assertOk();
        $this->get(route('analytics.mrr-retention'))->assertOk();
        $this->get(route('finance.salaries.index'))->assertOk();
        $this->get(route('finance.payroll.index'))->assertOk();
        $this->get(route('finance.analytics'))->assertOk();
        $this->get(route('alerts.index'))->assertOk();
    }

    public function test_staff_employee_cannot_access_analytics_finance_or_alerts(): void
    {
        $this->actingAs($this->staff);

        $this->get(route('analytics.index'))->assertForbidden();
        $this->get(route('analytics.cost-profit'))->assertForbidden();
        $this->get(route('analytics.technicians'))->assertForbidden();
        $this->get(route('analytics.mrr-retention'))->assertForbidden();
        $this->get(route('finance.salaries.index'))->assertForbidden();
        $this->get(route('finance.payroll.index'))->assertForbidden();
        $this->get(route('finance.analytics'))->assertForbidden();
        $this->get(route('alerts.index'))->assertForbidden();
    }

    public function test_technician_cannot_access_analytics_finance_or_alerts(): void
    {
        $this->actingAs($this->technician);

        $this->get(route('analytics.index'))->assertForbidden();
        $this->get(route('analytics.cost-profit'))->assertForbidden();
        $this->get(route('analytics.technicians'))->assertForbidden();
        $this->get(route('analytics.mrr-retention'))->assertForbidden();
        $this->get(route('finance.salaries.index'))->assertForbidden();
        $this->get(route('finance.payroll.index'))->assertForbidden();
        $this->get(route('finance.analytics'))->assertForbidden();
        $this->get(route('alerts.index'))->assertForbidden();
    }

    public function test_dashboard_data_endpoint_returns_revenue_and_executive_metrics_for_admin(): void
    {
        $this->actingAs($this->admin);

        $response = $this->getJson(route('dashboard.data'));
        $response->assertOk();
        $response->assertJsonStructure([
            'is_admin',
            'stats' => [
                'total_leads',
                'revenue_accepted',
                'revenue_this_month',
                'conversion_rate',
                'open_jobs',
            ],
            'revenueChart',
            'jobChart',
            'leadChart',
            'quotationChart',
            'upcomingJobs',
        ]);
        $this->assertTrue($response->json('is_admin'));
        $this->assertArrayHasKey('revenue_accepted', $response->json('stats'));
    }

    public function test_dashboard_data_endpoint_returns_work_only_data_without_revenue_for_staff(): void
    {
        $this->actingAs($this->staff);

        $response = $this->getJson(route('dashboard.data'));
        $response->assertOk();
        $response->assertJsonStructure([
            'is_admin',
            'stats' => [
                'open_jobs',
                'in_progress_jobs',
                'completed_jobs',
                'open_tickets',
            ],
            'jobChart',
            'upcomingJobs',
            'recentTickets',
            'attendance',
        ]);
        $this->assertFalse($response->json('is_admin'));
        $this->assertArrayNotHasKey('revenue_accepted', $response->json('stats'));
        $this->assertArrayNotHasKey('revenueChart', $response->json());
    }

    public function test_dashboard_view_renders_cleanly_for_both_roles(): void
    {
        // Admin
        $this->actingAs($this->admin);
        $resAdmin = $this->get(route('dashboard'));
        $resAdmin->assertOk();
        $resAdmin->assertSee('Operations &amp; Executive Command Center', false);

        // Staff
        $this->actingAs($this->staff);
        $resStaff = $this->get(route('dashboard'));
        $resStaff->assertOk();
        $resStaff->assertSee('Workforce Operations Command Center', false);
        $resStaff->assertDontSee('Accepted Revenue');
    }
}
