<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $products = DB::table('products')->get();
        $adminId = DB::table('users')->where('role', 'admin')->value('id') 
            ?? DB::table('users')->value('id');

        foreach ($products as $product) {
            $hasOpening = DB::table('stock_movements')
                ->where('product_id', $product->id)
                ->where('movement_type', 'opening')
                ->exists();

            if (!$hasOpening) {
                // If the product had existing movements, calculate difference or create opening balance
                $currentStock = (int) ($product->stock_quantity ?? 0);
                
                DB::table('stock_movements')->insert([
                    'product_id'     => $product->id,
                    'movement_type'  => 'opening',
                    'type'           => 'in',
                    'qty'            => $currentStock,
                    'quantity'       => $currentStock,
                    'unit_cost'      => $product->cost_price ?? 0.00,
                    'balance_after'  => $currentStock,
                    'reference_type' => null,
                    'reference_id'   => null,
                    'project_id'     => null,
                    'note'           => 'Opening stock balance',
                    'notes'          => 'Opening stock balance',
                    'created_by'     => $adminId,
                    'user_id'        => $adminId,
                    'created_at'     => $product->created_at ?? now(),
                    'updated_at'     => $product->updated_at ?? now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('stock_movements')->where('movement_type', 'opening')->delete();
    }
};
