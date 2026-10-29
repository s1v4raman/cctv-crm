<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Worker;
use App\Services\HeaderNotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccessHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function activeWorker(string $phone = '9840011122', string $name = 'Muthu Vel'): Worker
    {
        return Worker::create(['name' => $name, 'phone' => $phone, 'daily_rate' => 750, 'is_active' => true]);
    }

    private function workerUser(Worker $worker): User
    {
        $user = User::factory()->create(['name' => $worker->name, 'role' => 'worker', 'email' => "worker_{$worker->phone}@cctv-crm.local"]);
        $worker->update(['user_id' => $user->id]);

        return $user;
    }

    private function sentQuotation(): Quotation
    {
        $lead = Lead::create(['customer_name' => 'Asha Traders', 'phone' => '9876501234', 'email' => 'asha@example.com']);

        return Quotation::create([
            'lead_id' => $lead->id, 'quotation_no' => 'QT-ACCESS-0001', 'quotation_date' => Carbon::today(),
            'valid_until' => Carbon::today()->addDays(15), 'subtotal' => 10000, 'discount' => 0,
            'tax_percent' => 18, 'tax_amount' => 1800, 'total' => 11800, 'status' => 'sent',
        ]);
    }

    // --- Worker sign-in -------------------------------------------------------------

    public function test_unknown_phone_only_sends_a_request_and_does_not_sign_in(): void
    {
        $response = $this->post(route('login.worker'), ['name' => 'Stranger', 'phone' => '9000011111']);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertGuest();
        $this->assertDatabaseHas('workers', ['phone' => '9000011111', 'is_active' => false, 'registration_status' => 'pending']);
        $this->assertDatabaseMissing('users', ['email' => 'worker_9000011111@cctv-crm.local']);
    }

    public function test_worker_waiting_for_approval_cannot_sign_in(): void
    {
        Worker::create(['name' => 'Pending', 'phone' => '9000022222', 'daily_rate' => 750, 'is_active' => false, 'registration_status' => 'pending']);

        $this->post(route('login.worker'), ['name' => 'Pending', 'phone' => '9000022222'])
            ->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_approved_worker_signs_in_without_their_name_being_changed(): void
    {
        $worker = $this->activeWorker();

        $this->post(route('login.worker'), ['name' => 'Someone Else', 'phone' => '98400 11122'])
            ->assertRedirect(route('worker.portal'));

        $this->assertAuthenticated();
        $this->assertSame('Muthu Vel', $worker->fresh()->name);
        $this->assertTrue(auth()->user()->isWorker());
    }

    public function test_phone_sign_in_never_opens_an_office_account(): void
    {
        $worker = $this->activeWorker();
        $admin = User::factory()->create(['role' => 'admin']);
        $worker->update(['user_id' => $admin->id]);

        $this->post(route('login.worker'), ['name' => 'Muthu Vel', 'phone' => '9840011122'])
            ->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    // --- Worker role and internal pages ----------------------------------------------

    public function test_worker_is_sent_to_their_portal_instead_of_internal_pages(): void
    {
        $user = $this->workerUser($this->activeWorker());

        foreach (['/dashboard', '/leads', '/quotations', '/invoices', '/suppliers', '/employee-workforce', '/equipment'] as $page) {
            $this->actingAs($user)->get($page)->assertRedirect(route('worker.portal'));
        }

        $this->actingAs($user)->getJson('/dashboard/data')->assertForbidden();
    }

    public function test_office_users_still_open_internal_pages(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'staff']))->get('/leads')->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'technician']))->get('/equipment')->assertOk();
    }

    // --- Worker portal ---------------------------------------------------------------

    public function test_worker_portal_is_closed_to_non_workers_and_creates_nothing(): void
    {
        $this->activeWorker();
        $workers = Worker::count();

        foreach (['admin', 'staff', 'technician'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('worker.portal'))
                ->assertForbidden();
        }

        $this->assertSame($workers, Worker::count());
    }

    public function test_worker_portal_shows_only_the_signed_in_worker(): void
    {
        $this->activeWorker('9840099999', 'First Worker');
        $user = $this->workerUser($this->activeWorker('9840011122', 'Muthu Vel'));

        $this->actingAs($user)->get(route('worker.portal'))
            ->assertOk()
            ->assertSee('Muthu Vel')
            ->assertDontSee('First Worker');
    }

    public function test_turned_off_worker_cannot_use_the_portal(): void
    {
        $worker = $this->activeWorker();
        $user = $this->workerUser($worker);
        $worker->update(['is_active' => false]);

        $this->actingAs($user)->get(route('worker.portal'))->assertForbidden();
    }

    // --- Public customer links -------------------------------------------------------

    public function test_public_links_need_a_signature(): void
    {
        $quotation = $this->sentQuotation();

        $this->get("/view-quotation/{$quotation->id}")
            ->assertForbidden()
            ->assertSee('This link is no longer valid');

        $this->get(URL::signedRoute('quotations.public-pdf', ['quotation' => $quotation, 'format' => '1']))->assertOk();
    }

    // --- Admin approval of worker sign-ups -----------------------------------------

    public function test_admin_approves_a_sign_up_and_the_worker_can_then_sign_in(): void
    {
        $this->post(route('login.worker'), ['name' => 'Ravi Kumar', 'phone' => '9000033333']);
        $request = Worker::where('phone', '9000033333')->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('workers.index'))
            ->assertOk()
            ->assertSee('Sign-up requests (1)')
            ->assertSee('Ravi Kumar');

        $this->actingAs($admin)->patch(route('workers.approve', $request))->assertRedirect(route('workers.index'));
        $this->assertSame('approved', $request->fresh()->registration_status);
        $this->assertTrue($request->fresh()->is_active);

        auth()->logout();
        $this->post(route('login.worker'), ['name' => 'Ravi Kumar', 'phone' => '9000033333'])
            ->assertRedirect(route('worker.portal'));
        $this->get(route('worker.portal'))->assertOk()->assertSee('Ravi Kumar')->assertSee('My Work &amp; Pay', false);
    }

    public function test_admin_rejects_a_sign_up(): void
    {
        $request = Worker::create(['name' => 'Unknown', 'phone' => '9000044444', 'daily_rate' => 750, 'is_active' => false, 'registration_status' => 'pending']);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->delete(route('workers.reject', $request))
            ->assertRedirect(route('workers.index'));

        $this->assertSoftDeleted($request);
    }

    public function test_only_admins_can_approve_or_reject_and_only_requests(): void
    {
        $request = Worker::create(['name' => 'Waiting', 'phone' => '9000055555', 'daily_rate' => 750, 'is_active' => false, 'registration_status' => 'pending']);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->patch(route('workers.approve', $request))->assertForbidden();
        $this->actingAs($staff)->delete(route('workers.reject', $request))->assertForbidden();
        $this->assertSame('pending', $request->fresh()->registration_status);

        // An approved worker cannot be "rejected" (deleted) through the request button
        $approved = $this->activeWorker();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->delete(route('workers.reject', $approved))
            ->assertNotFound();
        $this->assertNotSoftDeleted($approved);
    }

    public function test_admin_is_notified_about_sign_up_requests(): void
    {
        Worker::create(['name' => 'New Helper', 'phone' => '9000066666', 'daily_rate' => 750, 'is_active' => false, 'registration_status' => 'pending']);

        $titles = collect(HeaderNotificationService::getNotificationsForUser(User::factory()->create(['role' => 'admin']))['items'])->pluck('title');

        $this->assertContains('Worker sign-up: New Helper', $titles);
    }

    public function test_worker_page_shows_their_pay_and_payments_received(): void
    {
        $worker = $this->activeWorker();
        $user = $this->workerUser($worker);
        $worker->wagePayments()->create(['payment_type' => 'advance', 'amount' => 500, 'payment_date' => Carbon::today(), 'payment_mode' => 'upi']);

        $this->actingAs($user)->get(route('worker.portal'))
            ->assertOk()
            ->assertSee('Paid to you')
            ->assertSee('Balance due')
            ->assertSee('Payments received')
            ->assertSee('UPI / GPay')
            ->assertSee('₹500', false);
    }
}
