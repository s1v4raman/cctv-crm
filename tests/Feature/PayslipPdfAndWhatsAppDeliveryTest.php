<?php

namespace Tests\Feature;

use App\Models\EmployeeSalary;
use App\Models\NotificationLog;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PayslipPdfAndWhatsAppDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;
    private Payroll $payroll;

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

        EmployeeSalary::create([
            'user_id'             => $this->employee->id,
            'base_salary_monthly' => 30000,
            'daily_rate'          => 1153.85,
            'weekly_rate'         => 6928.40,
            'hourly_rate'         => 144.23,
            'payment_method'      => 'bank_transfer',
            'bank_name'           => 'HDFC Bank',
            'bank_account_number' => '50100234891234',
            'bank_ifsc'           => 'HDFC0001234',
            'notes'               => 'Phone: 8789076658',
        ]);

        $this->payroll = Payroll::create([
            'payroll_number' => 'PAY-202609-001',
            'user_id'        => $this->employee->id,
            'period_type'    => 'monthly',
            'period_start'   => '2026-09-01',
            'period_end'     => '2026-09-30',
            'working_days'   => 26,
            'present_days'   => 24,
            'half_days'      => 1,
            'leave_days'     => 1,
            'absent_days'    => 0,
            'overtime_hours' => 4.0,
            'basic_pay'      => 30000.00,
            'overtime_pay'   => 720.00,
            'allowances'     => 1500.00,
            'deductions'     => 0.00,
            'net_salary'     => 32220.00,
            'status'         => 'approved',
            'created_by'     => $this->admin->id,
        ]);
    }

    /**
     * 1. Admin can download payslip as PDF file.
     */
    public function test_admin_can_download_payslip_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get(route('finance.payroll.pdf', $this->payroll));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * 2. Admin can stream payslip PDF for browser preview.
     */
    public function test_admin_can_stream_payslip_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get(route('finance.payroll.viewPdf', $this->payroll));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * 3. Admin can dispatch WhatsApp payslip and verify URL formatting.
     */
    public function test_admin_can_generate_whatsapp_dispatch_link(): void
    {
        $response = $this->actingAs($this->admin)->get(route('finance.payroll.sendWhatsApp', $this->payroll));

        $response->assertRedirect();
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('https://api.whatsapp.com/send', $targetUrl);
        $this->assertStringContainsString('8789076658', $targetUrl);
        $this->assertStringContainsString('PAY-202609-001', urldecode($targetUrl));
    }

    /**
     * 4. AJAX JSON request to send WhatsApp returns payload and writes NotificationLog.
     */
    public function test_whatsapp_ajax_request_logs_notification(): void
    {
        $response = $this->actingAs($this->admin)->getJson(route('finance.payroll.sendWhatsApp', $this->payroll));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'whatsapp_url',
            'web_url',
            'app_url',
            'pdf_url',
            'phone',
            'employee_name',
            'message',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'channel'        => 'whatsapp',
            'event_type'     => 'payslip_sent',
            'recipient_name' => 'Kesavan Employee',
            'status'         => 'sent',
            'reference_id'   => $this->payroll->id,
        ]);
    }

    /**
     * 5. Non-admin cannot access finance payroll PDF or WhatsApp actions.
     */
    public function test_staff_cannot_access_finance_payroll_management(): void
    {
        $this->actingAs($this->employee)
            ->get(route('finance.payroll.pdf', $this->payroll))
            ->assertStatus(403);

        $this->actingAs($this->employee)
            ->get(route('finance.payroll.sendWhatsApp', $this->payroll))
            ->assertStatus(403);
    }
}
