<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectWorkerAttendance;
use App\Models\Site;
use App\Models\User;
use App\Models\WagePayment;
use App\Models\WorkDay;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WagesSettlementActionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Worker $worker;
    protected Carbon $monday;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->worker = Worker::create([
            'name' => 'Pradeep',
            'phone' => '9876543210',
            'daily_rate' => 1500.00,
            'is_active' => true,
        ]);

        $this->monday = Carbon::parse('2026-10-19')->startOfWeek(); // Monday
    }

    public function test_pay_full_updates_data_and_settles_balance(): void
    {
        $company = Company::create(['name' => 'Test Company', 'code' => 'TC', 'is_active' => true]);
        $site = Site::create(['company_id' => $company->id, 'name' => 'Test Site', 'site_code' => 'TS-001', 'address' => 'Chennai', 'is_active' => true]);
        $project = Project::create([
            'project_code' => 'PRJ-TEST-001',
            'title' => 'Test Project',
            'name' => 'Test Project',
            'company_id' => $company->id,
            'site_id' => $site->id,
            'start_date' => $this->monday->toDateString(),
            'status' => 'in_progress',
        ]);

        // Prior week earning: ₹3,000. Prior week advance: ₹1,000 -> Brought forward = ₹2,000
        $workDayPrior = WorkDay::create([
            'project_id' => $project->id,
            'work_date' => (clone $this->monday)->subWeek()->toDateString(),
            'notes' => 'Prior week shift',
        ]);
        ProjectWorkerAttendance::create([
            'work_day_id' => $workDayPrior->id,
            'worker_id' => $this->worker->id,
            'cabling_metres' => 0,
            'daily_rate' => 1500.00,
            'wage_amount' => 3000.00,
            'cabling_amount' => 0.00,
            'total_amount' => 3000.00,
            'is_paid' => false,
        ]);
        WagePayment::create([
            'worker_id' => $this->worker->id,
            'payment_type' => 'advance',
            'amount' => 1000.00,
            'payment_date' => (clone $this->monday)->subWeek()->toDateString(),
            'week_start' => (clone $this->monday)->subWeek()->toDateString(),
            'week_end' => (clone $this->monday)->subWeek()->endOfWeek()->toDateString(),
            'payment_mode' => 'cash',
        ]);

        // Net Brought Forward into this week = 3000 - 1000 = 2000
        $this->assertEquals(2000.00, $this->worker->getBroughtForward($this->monday->toDateString()));

        // Check index page before settlement shows ₹2,000.00 due
        $responseBefore = $this->actingAs($this->admin)->get(route('wages.index', ['week' => $this->monday->toDateString()]));
        $responseBefore->assertOk()
            ->assertSee('₹2,000.00');

        // Settle with pay_full
        $settleResponse = $this->actingAs($this->admin)->post(route('wages.settle'), [
            'worker_id' => $this->worker->id,
            'settlement_action' => 'pay_full',
            'week_start' => $this->monday->toDateString(),
            'payment_mode' => 'cash',
            'reference_notes' => 'Paid in full on Sunday',
        ]);
        $settleResponse->assertRedirect();

        // After pay_full, check index page:
        // Total due is now 0.00, Paid this week shows ₹2,000.00, and status shows Settled
        $responseAfter = $this->actingAs($this->admin)->get(route('wages.index', ['week' => $this->monday->toDateString()]));
        $responseAfter->assertOk()
            ->assertSee('₹2,000.00')
            ->assertSee('Settled');

        $this->assertEquals(0.00, $this->worker->getRunningBalance());
    }

    public function test_pay_part_reduces_total_due_and_shows_remaining_balance(): void
    {
        $company = Company::create(['name' => 'Test Company', 'code' => 'TC', 'is_active' => true]);
        $site = Site::create(['company_id' => $company->id, 'name' => 'Test Site', 'site_code' => 'TS-001', 'address' => 'Chennai', 'is_active' => true]);
        $project = Project::create([
            'project_code' => 'PRJ-TEST-001',
            'title' => 'Test Project',
            'name' => 'Test Project',
            'company_id' => $company->id,
            'site_id' => $site->id,
            'start_date' => $this->monday->toDateString(),
            'status' => 'in_progress',
        ]);

        $workDay = WorkDay::create([
            'project_id' => $project->id,
            'work_date' => $this->monday->toDateString(),
            'notes' => 'Monday shift',
        ]);
        ProjectWorkerAttendance::create([
            'work_day_id' => $workDay->id,
            'worker_id' => $this->worker->id,
            'daily_rate' => 1500.00,
            'wage_amount' => 3000.00,
            'total_amount' => 3000.00,
            'is_paid' => false,
        ]);

        // Settle partial payment of ₹1,000 out of ₹3,000
        $settleResponse = $this->actingAs($this->admin)->post(route('wages.settle'), [
            'worker_id' => $this->worker->id,
            'settlement_action' => 'pay_part',
            'amount_to_pay' => 1000.00,
            'week_start' => $this->monday->toDateString(),
            'payment_mode' => 'cash',
            'reference_notes' => 'Part payment of 1000',
        ]);
        $settleResponse->assertRedirect();

        // Index page should show Paid: ₹1,000.00 and Remaining Payable: ₹2,000.00
        $response = $this->actingAs($this->admin)->get(route('wages.index', ['week' => $this->monday->toDateString()]));
        $response->assertOk()
            ->assertSee('₹1,000.00')
            ->assertSee('₹2,000.00')
            ->assertSee('Pay Part');

        $this->assertEquals(2000.00, $this->worker->getRunningBalance());
    }

    public function test_carry_forward_records_action_and_carries_to_next_week(): void
    {
        $company = Company::create(['name' => 'Test Company', 'code' => 'TC', 'is_active' => true]);
        $site = Site::create(['company_id' => $company->id, 'name' => 'Test Site', 'site_code' => 'TS-001', 'address' => 'Chennai', 'is_active' => true]);
        $project = Project::create([
            'project_code' => 'PRJ-TEST-001',
            'title' => 'Test Project',
            'name' => 'Test Project',
            'company_id' => $company->id,
            'site_id' => $site->id,
            'start_date' => $this->monday->toDateString(),
            'status' => 'in_progress',
        ]);

        $workDay = WorkDay::create([
            'project_id' => $project->id,
            'work_date' => $this->monday->toDateString(),
            'notes' => 'Monday shift',
        ]);
        ProjectWorkerAttendance::create([
            'work_day_id' => $workDay->id,
            'worker_id' => $this->worker->id,
            'daily_rate' => 1500.00,
            'wage_amount' => 1500.00,
            'total_amount' => 1500.00,
            'is_paid' => false,
        ]);

        // Carry forward action
        $settleResponse = $this->actingAs($this->admin)->post(route('wages.settle'), [
            'worker_id' => $this->worker->id,
            'settlement_action' => 'carry_forward',
            'week_start' => $this->monday->toDateString(),
            'payment_mode' => 'cash',
        ]);
        $settleResponse->assertRedirect();

        // In current week, status displays Carried Forward
        $responseThisWeek = $this->actingAs($this->admin)->get(route('wages.index', ['week' => $this->monday->toDateString()]));
        $responseThisWeek->assertOk()
            ->assertSee('Carried Forward');

        // In next week, the ₹1,500 appears in Brought Forward!
        $nextMonday = (clone $this->monday)->addWeek();
        $this->assertEquals(1500.00, $this->worker->getBroughtForward($nextMonday->toDateString()));

        $responseNextWeek = $this->actingAs($this->admin)->get(route('wages.index', ['week' => $nextMonday->toDateString()]));
        $responseNextWeek->assertOk()
            ->assertSee('₹1,500.00');
    }

    public function test_wages_table_shows_cabling_cameras_and_work_reports(): void
    {
        $company = Company::create(['name' => 'Test Company', 'code' => 'TC', 'is_active' => true]);
        $site = Site::create(['company_id' => $company->id, 'name' => 'Test Site', 'site_code' => 'TS-001', 'address' => 'Chennai', 'is_active' => true]);
        $project = Project::create([
            'project_code' => 'PRJ-TEST-002',
            'title' => 'CCTV Surveillance Project',
            'name' => 'CCTV Surveillance Project',
            'company_id' => $company->id,
            'site_id' => $site->id,
            'start_date' => $this->monday->toDateString(),
            'status' => 'in_progress',
        ]);

        $workDay = WorkDay::create([
            'project_id' => $project->id,
            'work_date' => $this->monday->toDateString(),
            'notes' => 'Monday camera installation',
        ]);

        ProjectWorkerAttendance::create([
            'work_day_id' => $workDay->id,
            'worker_id' => $this->worker->id,
            'daily_rate' => 1500.00,
            'cabling_metres' => 75.00,
            'cameras_installed' => 8,
            'total_amount' => 1500.00,
            'is_paid' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('wages.index', ['week' => $this->monday->toDateString()]));
        $response->assertOk()
            ->assertSee('75 m')
            ->assertSee('8')
            ->assertSee('Cameras');
    }
}

