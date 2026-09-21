<?php

namespace Tests\Feature;

use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalary;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceAndFinanceModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $technician;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->technician = User::factory()->create(['role' => 'technician']);
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    public function test_customer_cannot_access_attendance_or_finance_routes(): void
    {
        $this->actingAs($this->customer);

        $this->get(route('attendance.index'))->assertStatus(403);
        $this->get(route('finance.salaries.index'))->assertStatus(403);
        $this->get(route('finance.payroll.index'))->assertStatus(403);
        $this->get(route('finance.analytics'))->assertStatus(403);
    }

    public function test_internal_staff_can_view_attendance_and_clock_in_and_out(): void
    {
        $this->actingAs($this->staff);

        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('Employee Attendance Management System');

        // Clock in
        $clockInResponse = $this->post(route('attendance.clock-in'), [
            'notes' => 'Starting field support morning shift',
        ]);
        $clockInResponse->assertSessionHas('status');

        $this->assertDatabaseHas('employee_attendances', [
            'user_id' => $this->staff->id,
        ]);

        // Clock out
        $clockOutResponse = $this->post(route('attendance.clock-out'), [
            'notes' => 'Completed day shift',
        ]);
        $clockOutResponse->assertSessionHas('status');

        $attendance = EmployeeAttendance::where('user_id', $this->staff->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertContains($attendance->status, ['present', 'late']);
        $this->assertNotNull($attendance->clock_out);
        $this->assertNotNull($attendance->working_hours);
    }

    public function test_admin_can_manually_record_and_update_employee_attendance(): void
    {
        $this->actingAs($this->admin);

        $targetDate = now()->subDays(1)->toDateString();

        // Store manual attendance for technician
        $response = $this->post(route('attendance.store'), [
            'user_id'        => $this->technician->id,
            'date'           => $targetDate,
            'status'         => 'present',
            'clock_in'       => '09:00',
            'clock_out'      => '19:00', // 10 hours = 8 normal + 2 overtime
            'notes'          => 'Emergency CCTV installation at warehouse',
        ]);

        $response->assertSessionHas('status');

        $attendance = EmployeeAttendance::where('user_id', $this->technician->id)
            ->whereDate('date', $targetDate)
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals(10.0, (float) $attendance->working_hours);
        $this->assertEquals(2.0, (float) $attendance->overtime_hours);

        // Update attendance to 'half_day'
        $updateResponse = $this->put(route('attendance.update', $attendance), [
            'status'         => 'half_day',
            'clock_in'       => '09:00',
            'clock_out'      => '13:00',
            'notes'          => 'Requested half day for personal reasons',
        ]);

        $updateResponse->assertSessionHas('status');
        $this->assertEquals('half_day', $attendance->fresh()->status);
    }

    public function test_admin_can_configure_salary_structure_and_auto_derive_rates(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('finance.salaries.index'));
        $response->assertStatus(200);
        $response->assertSee('Employee Salary Master');

        // Configure technician's base salary: ₹26,000 monthly
        $updateResponse = $this->post(route('finance.salaries.update', $this->technician), [
            'base_salary_monthly' => 26000,
            'travel_allowance'    => 2000,
            'special_allowance'   => 1000,
            'deductions'          => 500,
            'payment_method'      => 'bank_transfer',
            'bank_name'           => 'HDFC Bank',
            'bank_account_number' => '50100234567890',
            'bank_ifsc'           => 'HDFC0001234',
        ]);

        $updateResponse->assertSessionHas('status');

        $salary = EmployeeSalary::where('user_id', $this->technician->id)->first();
        $this->assertNotNull($salary);
        $this->assertEquals(26000, $salary->base_salary_monthly);
        // Auto-derived: daily = 26000 / 26 = 1000
        $this->assertEquals(1000, $salary->daily_rate);
        // Auto-derived: weekly = 1000 * 6 = 6000
        $this->assertEquals(6000, $salary->weekly_rate);
        // Auto-derived: hourly = 1000 / 8 = 125
        $this->assertEquals(125, $salary->hourly_rate);
        // Auto-derived: overtime = 125 * 1.25 = 156.25
        $this->assertEquals(156.25, $salary->overtime_hourly_rate);
        // Total allowances: 2000 + 1000 = 3000
        $this->assertEquals(3000, $salary->total_allowances);
        // Net monthly: 26000 + 3000 - 500 = 28500
        $this->assertEquals(28500, $salary->net_monthly);
    }

    public function test_admin_can_generate_payroll_based_on_attendance_sync(): void
    {
        $this->actingAs($this->admin);

        // Setup technician salary structure
        $salary = EmployeeSalary::create([
            'user_id'              => $this->technician->id,
            'base_salary_monthly'  => 26000,
            'daily_rate'           => 1000,
            'weekly_rate'          => 6000,
            'hourly_rate'          => 125,
            'overtime_hourly_rate' => 150,
            'travel_allowance'     => 1500,
            'special_allowance'    => 500,
            'deductions'           => 200,
            'payment_method'       => 'upi',
            'upi_id'               => 'tech@okhdfcbank',
        ]);

        // Create 3 attendance days for this week: 2 present with 4 overtime hours total
        $startDate = now()->startOfWeek()->toDateString();
        $endDate   = now()->endOfWeek()->toDateString();

        EmployeeAttendance::create([
            'user_id'        => $this->technician->id,
            'date'           => $startDate,
            'status'         => 'present',
            'working_hours'  => 10,
            'overtime_hours' => 2,
        ]);

        EmployeeAttendance::create([
            'user_id'        => $this->technician->id,
            'date'           => Carbon::parse($startDate)->addDay()->toDateString(),
            'status'         => 'present',
            'working_hours'  => 10,
            'overtime_hours' => 2,
        ]);

        // Generate Weekly Payroll
        $generateResponse = $this->post(route('finance.payroll.generate'), [
            'period_type'  => 'weekly',
            'period_start' => $startDate,
            'period_end'   => $endDate,
            'user_id'      => $this->technician->id,
        ]);

        $generateResponse->assertSessionHas('status');

        $payroll = Payroll::where('user_id', $this->technician->id)
            ->where('period_type', 'weekly')
            ->first();

        $this->assertNotNull($payroll);
        $this->assertEquals(2, $payroll->present_days);
        $this->assertEquals(4, $payroll->overtime_hours);
        // 2 days * 1000 daily rate = 2000 basic pay
        $this->assertEquals(2000, $payroll->basic_pay);
        // 4 hours * 150 OT rate = 600 OT pay
        $this->assertEquals(600, $payroll->overtime_pay);
        $this->assertEquals('draft', $payroll->status);

        // View official payslip
        $showResponse = $this->get(route('finance.payroll.show', $payroll));
        $showResponse->assertStatus(200);
        $showResponse->assertSee($payroll->payroll_number);
        $showResponse->assertSee('Salary Payslip');
        $showResponse->assertSee($this->technician->name);

        // Update Payroll status to 'paid'
        $statusResponse = $this->post(route('finance.payroll.updateStatus', $payroll), [
            'status'            => 'paid',
            'payment_date'      => now()->toDateString(),
            'payment_reference' => 'UTR-889922001',
            'notes'             => 'Weekly wages paid via UPI',
        ]);

        $statusResponse->assertSessionHas('status');
        $this->assertEquals('paid', $payroll->fresh()->status);
        $this->assertEquals('UTR-889922001', $payroll->fresh()->payment_reference);
    }

    public function test_salary_analytics_supports_monthly_weekly_and_per_day_dimensions(): void
    {
        $this->actingAs($this->admin);

        // Assign salaries to staff and technician
        EmployeeSalary::create([
            'user_id'             => $this->staff->id,
            'base_salary_monthly' => 30000,
            'daily_rate'          => 1153.85,
            'weekly_rate'         => 6923.08,
            'hourly_rate'         => 144.23,
            'payment_method'      => 'bank_transfer',
        ]);

        EmployeeSalary::create([
            'user_id'             => $this->technician->id,
            'base_salary_monthly' => 25000,
            'daily_rate'          => 961.54,
            'weekly_rate'         => 5769.23,
            'hourly_rate'         => 120.19,
            'payment_method'      => 'bank_transfer',
        ]);

        // Monthly view
        $monthlyRes = $this->get(route('finance.analytics', ['mode' => 'monthly']));
        $monthlyRes->assertStatus(200);
        $monthlyRes->assertSee('Monthly Salary Commitment');
        $monthlyRes->assertSee('55,000'); // Staff 30,000 + Tech 25,000

        // Weekly view
        $weeklyRes = $this->get(route('finance.analytics', ['mode' => 'weekly']));
        $weeklyRes->assertStatus(200);
        $weeklyRes->assertSee('Weekly Payroll Commitment');

        // Per-Day view
        $dailyRes = $this->get(route('finance.analytics', ['mode' => 'daily']));
        $dailyRes->assertStatus(200);
        $dailyRes->assertSee('Daily Operational Burn');
    }

    public function test_quick_mark_attendance_status_updates_record_and_hours(): void
    {
        $this->actingAs($this->admin);

        $targetDate = now()->subDays(2)->toDateString();

        // 1-Click quick mark Present
        $response = $this->post(route('attendance.quick-mark'), [
            'user_id' => $this->technician->id,
            'date'    => $targetDate,
            'status'  => 'present',
        ]);

        $response->assertSessionHas('status');

        $record = EmployeeAttendance::where('user_id', $this->technician->id)
            ->whereDate('date', $targetDate)
            ->first();

        $this->assertNotNull($record);
        $this->assertEquals('present', $record->status);
        $this->assertEquals(8.0, (float) $record->total_hours);

        // 1-Click quick mark Half Day
        $halfDayResponse = $this->post(route('attendance.quick-mark'), [
            'user_id' => $this->technician->id,
            'date'    => $targetDate,
            'status'  => 'half_day',
        ]);

        $halfDayResponse->assertSessionHas('status');
        $this->assertEquals('half_day', $record->fresh()->status);
        $this->assertEquals(4.0, (float) $record->fresh()->total_hours);
    }

    public function test_weekly_attendance_timesheet_and_analytics_view(): void
    {
        $this->actingAs($this->admin);

        $weekStart = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();

        // Create attendance for this week
        EmployeeAttendance::create([
            'user_id'     => $this->technician->id,
            'date'        => $weekStart,
            'status'      => 'present',
            'clock_in'    => '09:00:00',
            'clock_out'   => '18:00:00',
            'total_hours' => 8.0,
        ]);

        $response = $this->get(route('attendance.index', ['tab' => 'weekly', 'week_date' => $weekStart]));
        $response->assertStatus(200);
        $response->assertSee('Weekly Timesheet &amp; Analysis', false);
        $response->assertSee('Weekly Workforce Hours');
        $response->assertSee('Daily Attendance Trend');
        $response->assertSee($this->technician->name);
    }

    public function test_monthly_attendance_matrix_and_analytics_view(): void
    {
        $this->actingAs($this->admin);

        $currentMonth = now()->format('Y-m');

        // Create attendance for this month
        EmployeeAttendance::create([
            'user_id'     => $this->staff->id,
            'date'        => now()->startOfMonth()->toDateString(),
            'status'      => 'present',
            'clock_in'    => '09:00:00',
            'clock_out'   => '18:00:00',
            'total_hours' => 8.0,
        ]);

        $response = $this->get(route('attendance.index', ['tab' => 'monthly', 'month' => $currentMonth]));
        $response->assertStatus(200);
        $response->assertSee('Monthly Matrix &amp; Analytics', false);
        $response->assertSee('Monthly Attendance Matrix &amp; Heatmap', false);
        $response->assertSee('Punctuality Score');
        $response->assertSee($this->staff->name);
    }

    public function test_batch_store_bulk_day_and_date_range_fill(): void
    {
        $this->actingAs($this->admin);

        $date1 = now()->subDays(5)->toDateString();

        // 1. Bulk day fill: Mark unmarked staff as Present
        $bulkDayResponse = $this->post(route('attendance.batch-store'), [
            'mode'   => 'bulk_day',
            'date'   => $date1,
            'status' => 'present',
            'target' => 'unmarked',
        ]);

        $bulkDayResponse->assertSessionHas('status');

        $record1 = EmployeeAttendance::where('user_id', $this->technician->id)
            ->whereDate('date', $date1)
            ->first();
        $this->assertNotNull($record1);
        $this->assertEquals('present', $record1->status);

        // 2. Bulk range fill for an employee across 3 days
        $startDate = now()->subDays(4)->toDateString();
        $endDate = now()->subDays(2)->toDateString();

        $rangeResponse = $this->post(route('attendance.batch-store'), [
            'mode'         => 'bulk_range',
            'user_id'      => $this->technician->id,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'status'       => 'present',
            'daily_hours'  => 8.0,
            'skip_sundays' => 1,
        ]);

        $rangeResponse->assertSessionHas('status');

        $rangeRecord = EmployeeAttendance::where('user_id', $this->technician->id)
            ->whereDate('date', $startDate)
            ->first();
        $this->assertNotNull($rangeRecord);
        $this->assertEquals('present', $rangeRecord->status);
    }
}
