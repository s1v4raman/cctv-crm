<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seeder_creates_reference_data_but_no_logins(): void
    {
        $this->seed(ProductionSeeder::class);

        $this->assertSame(0, User::count());
        $this->assertSame(3, Company::count());
        $this->assertGreaterThan(0, Product::count());
    }

    public function test_production_seeder_does_not_overwrite_edited_products(): void
    {
        $this->seed(ProductionSeeder::class);
        $product = Product::first();
        $product->update(['unit_price' => 12345, 'stock_quantity' => 77]);

        $this->seed(ProductionSeeder::class);

        $product->refresh();
        $this->assertEquals(12345, $product->unit_price);
        $this->assertSame(77, (int) $product->stock_quantity);
    }

    public function test_demo_access_link_is_not_available_outside_local(): void
    {
        $this->get('/demo/projects')->assertNotFound();
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'reviewer@cctvcrm.com']);
    }

    public function test_demo_access_link_still_works_in_local(): void
    {
        $this->app['env'] = 'local';

        $this->get('/demo/projects')->assertRedirect(route('projects.index'));
        $this->assertAuthenticated();
    }
}
