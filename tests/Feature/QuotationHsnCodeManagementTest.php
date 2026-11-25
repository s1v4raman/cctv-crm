<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationHsnCodeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $technician;
    protected User $employee;
    protected Lead $lead;
    protected Product $product;
    protected Quotation $quotation;
    protected QuotationItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->technician = User::factory()->create([
            'role' => 'technician',
        ]);

        $this->employee = User::factory()->create([
            'role' => 'staff',
        ]);

        $this->lead = Lead::create([
            'customer_name' => 'Acme Hospital',
            'phone' => '9876543210',
            'email' => 'contact@acme.com',
            'status' => 'new',
            'created_by' => $this->admin->id,
        ]);

        $this->product = Product::create([
            'sku' => 'CAM-4MP-001',
            'name' => '4MP ColorVu Bullet Camera',
            'unit_price' => 3500.00,
            'hsn_code' => '8525',
            'is_active' => true,
        ]);

        $this->quotation = Quotation::create([
            'lead_id' => $this->lead->id,
            'created_by' => $this->admin->id,
            'quotation_no' => 'QT-TEST-001',
            'quotation_date' => now()->format('Y-m-d'),
            'subtotal' => 7000.00,
            'total' => 8260.00,
            'status' => 'draft',
            'pricing_mode' => 'itemized',
            'pdf_format' => '1',
        ]);

        $this->item = QuotationItem::create([
            'quotation_id' => $this->quotation->id,
            'product_id' => $this->product->id,
            'item_name' => '4MP ColorVu Bullet Camera',
            'quantity' => 2,
            'unit' => 'Nos',
            'unit_price' => 3500.00,
            'total' => 7000.00,
            'hsn_code' => '8525',
        ]);
    }

    public function test_admin_can_view_and_update_hsn_code_for_quotation_item_and_product(): void
    {
        $this->actingAs($this->admin);

        // 1. Admin views the quotation page and sees HSN code
        $viewResponse = $this->get(route('quotations.show', $this->quotation));
        $viewResponse->assertOk();
        $viewResponse->assertSee('HSN/SAC');
        $viewResponse->assertSee('Manage HSN / SAC Code');

        // 2. Admin updates the HSN code to an 8-digit numeric code
        $updateResponse = $this->postJson(route('quotation-items.update-hsn', $this->item), [
            'hsn_code' => '85258900',
            'update_product' => 1,
        ]);

        $updateResponse->assertOk();
        $updateResponse->assertJson([
            'success' => true,
            'hsn_code' => '85258900',
        ]);

        $this->item->refresh();
        $this->product->refresh();

        $this->assertEquals('85258900', $this->item->hsn_code);
        $this->assertEquals('85258900', $this->product->hsn_code);
    }

    public function test_admin_can_delete_hsn_code(): void
    {
        $this->actingAs($this->admin);

        // Delete / clear the HSN code
        $deleteResponse = $this->deleteJson(route('quotation-items.delete-hsn', $this->item), [
            'delete_from_product' => 1,
        ]);

        $deleteResponse->assertOk();
        $deleteResponse->assertJson([
            'success' => true,
            'hsn_code' => null,
        ]);

        $this->item->refresh();
        $this->product->refresh();

        $this->assertNull($this->item->getRawOriginal('hsn_code'));
        $this->assertNull($this->product->hsn_code);
    }

    public function test_hsn_code_max_size_is_eight_numbers_and_rejects_nine_or_more(): void
    {
        $this->actingAs($this->admin);

        // A. 9 digits must fail validation
        $response9Digits = $this->postJson(route('quotation-items.update-hsn', $this->item), [
            'hsn_code' => '123456789',
        ]);
        $response9Digits->assertStatus(422);
        $response9Digits->assertJsonValidationErrors(['hsn_code']);

        // B. Non-numeric / letters must fail validation
        $responseLetters = $this->postJson(route('quotation-items.update-hsn', $this->item), [
            'hsn_code' => 'HSN8525',
        ]);
        $responseLetters->assertStatus(422);
        $responseLetters->assertJsonValidationErrors(['hsn_code']);

        // C. Valid numeric sizes (2 to 8 digits) must succeed
        $validCodes = ['85', '8525', '852589', '85258900'];
        foreach ($validCodes as $validCode) {
            $resp = $this->postJson(route('quotation-items.update-hsn', $this->item), [
                'hsn_code' => $validCode,
            ]);
            $resp->assertOk();
            $this->item->refresh();
            $this->assertEquals($validCode, $this->item->hsn_code);
        }
    }

    public function test_employee_and_technician_cannot_update_or_delete_hsn_code(): void
    {
        // 1. Technician attempt to update
        $this->actingAs($this->technician);
        $techUpdate = $this->postJson(route('quotation-items.update-hsn', $this->item), [
            'hsn_code' => '85258900',
        ]);
        $techUpdate->assertStatus(403);

        // 2. Technician attempt to delete
        $techDelete = $this->deleteJson(route('quotation-items.delete-hsn', $this->item));
        $techDelete->assertStatus(403);

        // 3. Employee attempt to update
        $this->actingAs($this->employee);
        $empUpdate = $this->postJson(route('quotation-items.update-hsn', $this->item), [
            'hsn_code' => '85258900',
        ]);
        $empUpdate->assertStatus(403);

        // 4. Employee attempt to delete
        $empDelete = $this->deleteJson(route('quotation-items.delete-hsn', $this->item));
        $empDelete->assertStatus(403);

        // 5. Employee & Technician can view quotation with read-only badge
        $viewResp = $this->get(route('quotations.show', $this->quotation));
        $viewResp->assertOk();
        $viewResp->assertSee('8525');
        // Admin-only management modal must not be rendered for non-admins
        $viewResp->assertDontSee('id="hsn-manager-modal"', false);
    }
}
