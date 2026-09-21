<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Product;
use App\Models\ServiceTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontAndCustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_homepage_renders_successfully_with_products_and_categories(): void
    {
        Product::create([
            'sku' => 'HK-4K-COLORVU',
            'name' => 'Hikvision 4K ColorVu AI Bullet Camera',
            'unit' => 'Nos',
            'cost_price' => 3000,
            'unit_price' => 5200,
            'stock_quantity' => 15,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Innovative IT');
        $response->assertSee('Our Services');
        $response->assertSee('Corporate Solutions');
    }

    public function test_storefront_search_and_category_filter(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Search Solutions & Services', false);
    }

    public function test_guest_public_inquiry_creates_lead_and_service_ticket(): void
    {
        $response = $this->post('/public/inquire', [
            'customer_name' => 'Vikram Patel',
            'phone' => '9876543210',
            'email' => 'vikram@example.com',
            'site_address' => 'Indiranagar 100ft Road, Bangalore',
            'service_type' => 'Villa CCTV Package Inquiry',
            'product_name' => 'Hikvision 4K ColorVu AI Bullet Camera',
            'property_type' => 'Home / Villa',
            'camera_count' => '4 to 8 Cameras',
            'notes' => 'Need 6 cameras with 30 days recording.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('leads', [
            'customer_name' => 'Vikram Patel',
            'phone' => '9876543210',
            'email' => 'vikram@example.com',
            'source' => 'website',
        ]);

        $this->assertDatabaseHas('service_tickets', [
            'priority' => 'high',
            'status' => 'open',
        ]);
    }

    public function test_new_customer_registration_creates_account_and_redirects_to_portal_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Meera Nair',
            'email' => 'meera@example.com',
            'phone' => '9123456780',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'meera@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('customer', $user->role);
        $this->assertNotNull($user->lead_id);

        $response->assertRedirect(route('portal.dashboard', absolute: false));
    }

    public function test_logged_in_customer_sees_portal_navigation_on_homepage(): void
    {
        $customer = User::factory()->create([
            'name' => 'Aarav Gupta',
            'email' => 'aarav@example.com',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->get('/');

        $response->assertStatus(200);
        $response->assertSee('Customer Portal');
        $response->assertSee('Hello, Aarav');
    }
}
