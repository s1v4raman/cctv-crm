<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Tests\TestCase;

/**
 * A supplier can be added from the purchase order form without leaving it.
 */
class PurchaseOrderAddSupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_form_has_the_add_new_supplier_button_and_popup(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('purchase-orders.create'))
            ->assertOk()
            ->assertSee('Add new supplier')
            ->assertSee("open-modal', 'add-supplier'", false)
            ->assertSee('newSupplierForm(' . Js::from(route('suppliers.store')), false)
            ->assertSee('Save supplier');
    }

    public function test_popup_saves_the_supplier_and_gets_back_what_the_list_needs(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)->postJson(route('suppliers.store'), [
            'name' => 'Sri Murugan CCTV Distributors',
            'company_name' => 'SM Distributors Pvt Ltd',
            'phone' => '9840012345',
            'gst_number' => '33AAKFS1234L1Z9',
            'payment_terms' => 'Net 30 Days',
            'city' => 'Chennai',
        ]);

        $supplier = Supplier::firstOrFail();
        $response->assertCreated()->assertExactJson([
            'id' => $supplier->id,
            'label' => 'Sri Murugan CCTV Distributors (SM Distributors Pvt Ltd)',
            'message' => "Supplier 'Sri Murugan CCTV Distributors' added and selected.",
        ]);
        $this->assertTrue($supplier->is_active);
        $this->assertSame('Chennai', $supplier->city);

        // It is in the supplier list the next time the order form opens.
        $this->actingAs($staff)->get(route('purchase-orders.create'))
            ->assertSee('Sri Murugan CCTV Distributors (SM Distributors Pvt Ltd)');
    }

    public function test_popup_gets_field_errors_back(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->postJson(route('suppliers.store'), [
            'name' => '',
            'email' => 'not-an-email',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email']);

        $this->assertSame(0, Supplier::count());
    }

    public function test_supplier_page_form_still_goes_to_the_supplier_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('suppliers.store'), ['name' => 'Kumar Electricals']);

        $response->assertRedirect(route('suppliers.show', Supplier::firstOrFail()));
    }

    public function test_technician_cannot_add_a_supplier(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);

        $this->actingAs($technician)->postJson(route('suppliers.store'), ['name' => 'Kumar Electricals'])
            ->assertForbidden();

        $this->assertSame(0, Supplier::count());
    }
}
