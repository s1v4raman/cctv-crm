<?php

namespace Tests\Feature;

use App\Models\EmployeeAttendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeaveManagementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;
    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'     => 'Super Admin',
            'email'    => 'admin@cctv.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        $this->employee = User::factory()->create([
            'name'     => 'Kesavan Employee',
            'email'    => 'kesavan@gmail.com',
            'password' => Hash::make('kesavan123'),
            'role'     => 'staff',
        ]);

        $this->technician = User::factory()->create([
            'name'     => 'Bob Miller',
            'email'    => 'technician@cctv.com',
            'password' => Hash::make('password123'),
            'role'     => 'technician',
        ]);
    }

    /**
     * 1. Employee can access leave management hub and view quota cards.
     */
    public function test_employee_can_view_leave_management_hub_with_quotas(): void
    {
        $response = $this->actingAs($this->employee)->get(route('leaves.index'));

        $response->assertOk();
        $response->assertSee('Employee Leave Management');
        $response->assertSee('Casual Leave (CL)');
        $response->assertSee('Sick Leave (SL)');
        $response->assertSee('Earned / Paid Leave');
    }

    /**
     * 2. Employee can submit a single-day and multi-day leave application.
     */
    public function test_employee_can_submit_leave_application(): void
    {
        $startDate = now()->addDays(3)->toDateString();
        $endDate = now()->addDays(5)->toDateString();

        $response = $this->actingAs($this->employee)->post(route('leaves.store'), [
            'leave_type' => 'casual',
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'reason'     => 'Attending family function in native town.',
        ]);

        $response->assertRedirect(route('leaves.index', ['tab' => 'my_leaves']));
        $leave = LeaveRequest::where('user_id', $this->employee->id)->first();
        $this->assertNotNull($leave);
        $this->assertEquals('casual', $leave->leave_type);
        $this->assertEquals($startDate, \Carbon\Carbon::parse($leave->start_date)->toDateString());
        $this->assertEquals($endDate, \Carbon\Carbon::parse($leave->end_date)->toDateString());
        $this->assertEquals('pending', $leave->status);
    }

    /**
     * 3. Employee can submit a half-day leave request.
     */
    public function test_employee_can_submit_half_day_leave(): void
    {
        $leaveDate = now()->addDays(2)->toDateString();

        $response = $this->actingAs($this->employee)->post(route('leaves.store'), [
            'leave_type'        => 'sick',
            'start_date'        => $leaveDate,
            'end_date'          => $leaveDate,
            'is_half_day'       => 1,
            'half_day_session'  => 'afternoon',
            'reason'            => 'Dental appointment in the afternoon.',
        ]);

        $response->assertRedirect(route('leaves.index', ['tab' => 'my_leaves']));
        $leave = LeaveRequest::where('user_id', $this->employee->id)->where('leave_type', 'sick')->first();
        $this->assertNotNull($leave);
        $this->assertEquals(0.5, (float) $leave->days_count);
        $this->assertTrue($leave->is_half_day);
        $this->assertEquals('afternoon', $leave->half_day_session);
        $this->assertEquals('pending', $leave->status);
    }

    /**
     * 4. Validation prevents invalid date ranges.
     */
    public function test_validation_prevents_invalid_date_range(): void
    {
        $response = $this->actingAs($this->employee)->post(route('leaves.store'), [
            'leave_type' => 'casual',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date'   => now()->addDays(2)->toDateString(), // End before start
            'reason'     => 'Invalid test',
        ]);

        $response->assertSessionHasErrors(['end_date']);
    }

    /**
     * 5. Employee can cancel their own pending leave request.
     */
    public function test_employee_can_cancel_their_own_pending_leave(): void
    {
        $leave = LeaveRequest::create([
            'user_id'    => $this->employee->id,
            'leave_type' => 'casual',
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date'   => now()->addDays(4)->toDateString(),
            'days_count' => 1.0,
            'reason'     => 'Trip planned',
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($this->employee)->delete(route('leaves.cancel', $leave));

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'id'     => $leave->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * 6. Employee cannot cancel someone else's leave request.
     */
    public function test_employee_cannot_cancel_others_leave(): void
    {
        $leave = LeaveRequest::create([
            'user_id'    => $this->technician->id,
            'leave_type' => 'sick',
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date'   => now()->addDays(3)->toDateString(),
            'days_count' => 1.0,
            'reason'     => 'Fever',
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($this->employee)->delete(route('leaves.cancel', $leave));

        $response->assertStatus(403);
        $this->assertEquals('pending', $leave->fresh()->status);
    }

    /**
     * 7. Admin can approve leave and auto-synchronize attendance records.
     */
    public function test_admin_can_approve_leave_and_auto_mark_attendance(): void
    {
        $startDate = now()->next(\Carbon\CarbonInterface::MONDAY);
        $endDate = $startDate->copy()->addDay();

        $leave = LeaveRequest::create([
            'user_id'    => $this->employee->id,
            'leave_type' => 'casual',
            'start_date' => $startDate->toDateString(),
            'end_date'   => $endDate->toDateString(),
            'days_count' => 2.0,
            'reason'     => 'Personal emergency and family vacation',
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('leaves.approve', $leave));

        $response->assertRedirect();
        $this->assertEquals('approved', $leave->fresh()->status);
        $this->assertEquals($this->admin->id, $leave->fresh()->actioned_by);

        // Verify attendance records were auto-created as on_leave
        $attendance1 = EmployeeAttendance::where('user_id', $this->employee->id)
            ->whereDate('date', $startDate->toDateString())
            ->first();

        $this->assertNotNull($attendance1);
        $this->assertEquals('on_leave', $attendance1->status);
        $this->assertStringContainsString('Approved Casual Leave', $attendance1->notes);
    }

    /**
     * 8. Admin can reject leave request with reason.
     */
    public function test_admin_can_reject_leave_request_with_reason(): void
    {
        $leave = LeaveRequest::create([
            'user_id'    => $this->employee->id,
            'leave_type' => 'casual',
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date'   => now()->addDays(8)->toDateString(),
            'days_count' => 2.0,
            'reason'     => 'Vacation',
            'status'     => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('leaves.reject', $leave), [
            'rejection_reason' => 'High volume of urgent on-site CCTV installations scheduled.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('rejected', $leave->fresh()->status);
        $this->assertEquals('High volume of urgent on-site CCTV installations scheduled.', $leave->fresh()->rejection_reason);
        $this->assertEquals($this->admin->id, $leave->fresh()->actioned_by);
    }

    /**
     * 9. Staff or Technician cannot approve or reject leaves (403 Forbidden).
     */
    public function test_non_admin_cannot_approve_or_reject_leaves(): void
    {
        $leave = LeaveRequest::create([
            'user_id'    => $this->technician->id,
            'leave_type' => 'casual',
            'start_date' => now()->addDays(6)->toDateString(),
            'end_date'   => now()->addDays(6)->toDateString(),
            'days_count' => 1.0,
            'reason'     => 'Family work',
            'status'     => 'pending',
        ]);

        // Attempt approve as staff
        $this->actingAs($this->employee)
            ->patch(route('leaves.approve', $leave))
            ->assertStatus(403);

        // Attempt reject as technician
        $this->actingAs($this->technician)
            ->patch(route('leaves.reject', $leave), ['rejection_reason' => 'No reason'])
            ->assertStatus(403);
    }
}
