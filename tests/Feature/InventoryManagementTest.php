<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_user_can_view_inventory_overview(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $product1 = Product::create([
            'name' => 'Hikvision 4MP Dome Camera',
            'sku' => 'DS-2CD2143G2-I',
            'category' => 'Camera',
            'brand' => 'Hikvision',
            'unit' => 'Nos',
            'cost_price' => 2500,
            'unit_price' => 3200,
            'stock_quantity' => 12,
            'min_stock_alert' => 5,
        ]);

        $product2 = Product::create([
            'name' => '4TB Surveillance Hard Drive',
            'sku' => 'WD40PURZ',
            'category' => 'Storage / HDD',
            'brand' => 'Western Digital',
            'unit' => 'Nos',
            'cost_price' => 6000,
            'unit_price' => 7500,
            'stock_quantity' => 2,
            'min_stock_alert' => 3, // Low stock
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('inventory.index'));

        $response->assertOk();
        $response->assertSee('Warehouse & Inventory Management', false);
        $response->assertSee('Hikvision 4MP Dome Camera');
        $response->assertSee('4TB Surveillance Hard Drive');
        $response->assertSee('Low: 2 Nos');
    }

    public function test_admin_can_adjust_stock_with_movement_logging(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $product = Product::create([
            'name' => '8-Channel PoE NVR',
            'unit' => 'Nos',
            'cost_price' => 8000,
            'unit_price' => 10500,
            'stock_quantity' => 5,
            'min_stock_alert' => 2,
        ]);

        // Test Stock In
        $response = $this
            ->actingAs($admin)
            ->post(route('inventory.adjust', $product), [
                'type' => 'in',
                'quantity' => 10,
                'notes' => 'Received from Distributor Invoice #9981',
            ]);

        $response->assertRedirect();
        $this->assertEquals(15, $product->fresh()->stock_quantity);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 10,
            'balance_after' => 15,
            'notes' => 'Received from Distributor Invoice #9981',
            'user_id' => $admin->id,
        ]);

        // Test Stock Out
        $response = $this
            ->actingAs($admin)
            ->post(route('inventory.adjust', $product), [
                'type' => 'out',
                'quantity' => 3,
                'notes' => 'Manual dispatch',
            ]);

        $response->assertRedirect();
        $this->assertEquals(12, $product->fresh()->stock_quantity);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 3,
            'balance_after' => 12,
        ]);
    }

    public function test_cannot_dispatch_more_than_available_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $product = Product::create([
            'name' => '12V 5A CCTV Power Supply',
            'unit' => 'Nos',
            'cost_price' => 450,
            'unit_price' => 750,
            'stock_quantity' => 2,
            'min_stock_alert' => 5,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('inventory.adjust', $product), [
                'type' => 'out',
                'quantity' => 5,
                'notes' => 'Exceeding stock dispatch attempt',
            ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertEquals(2, $product->fresh()->stock_quantity);
    }

    public function test_can_view_stock_movement_logs(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $product = Product::create([
            'name' => 'Cat6 UTP Cable Box 305m',
            'unit' => 'Box',
            'cost_price' => 4200,
            'unit_price' => 5500,
            'stock_quantity' => 10,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 10,
            'balance_after' => 10,
            'notes' => 'Initial stock load',
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('inventory.movements'));

        $response->assertOk();
        $response->assertSee('Inventory Movement & Audit Logs', false);
        $response->assertSee('Cat6 UTP Cable Box 305m');
        $response->assertSee('Initial stock load');
    }
}
