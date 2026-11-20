<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add project_id to purchase_orders
        if (Schema::hasTable('purchase_orders') && !Schema::hasColumn('purchase_orders', 'project_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->foreignId('project_id')->nullable()->after('supplier_id')->constrained('projects')->nullOnDelete();
            });
        }

        // 2. Add bom_item_id to purchase_order_items
        if (Schema::hasTable('purchase_order_items') && !Schema::hasColumn('purchase_order_items', 'bom_item_id')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->foreignId('bom_item_id')->nullable()->after('product_id')->constrained('project_materials')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_order_items') && Schema::hasColumn('purchase_order_items', 'bom_item_id')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->dropForeign(['bom_item_id']);
                $table->dropColumn('bom_item_id');
            });
        }

        if (Schema::hasTable('purchase_orders') && Schema::hasColumn('purchase_orders', 'project_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            });
        }
    }
};
