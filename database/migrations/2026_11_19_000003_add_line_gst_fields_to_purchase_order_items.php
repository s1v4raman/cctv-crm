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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_order_items', 'hsn_code')) {
                $table->string('hsn_code', 20)->nullable()->after('sku');
            }
            if (!Schema::hasColumn('purchase_order_items', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(18.00)->after('total_cost');
            }
            if (!Schema::hasColumn('purchase_order_items', 'cgst_rate')) {
                $table->decimal('cgst_rate', 5, 2)->default(0.00)->after('tax_rate');
            }
            if (!Schema::hasColumn('purchase_order_items', 'cgst_amount')) {
                $table->decimal('cgst_amount', 12, 2)->default(0.00)->after('cgst_rate');
            }
            if (!Schema::hasColumn('purchase_order_items', 'sgst_rate')) {
                $table->decimal('sgst_rate', 5, 2)->default(0.00)->after('cgst_amount');
            }
            if (!Schema::hasColumn('purchase_order_items', 'sgst_amount')) {
                $table->decimal('sgst_amount', 12, 2)->default(0.00)->after('sgst_rate');
            }
            if (!Schema::hasColumn('purchase_order_items', 'igst_rate')) {
                $table->decimal('igst_rate', 5, 2)->default(0.00)->after('sgst_amount');
            }
            if (!Schema::hasColumn('purchase_order_items', 'igst_amount')) {
                $table->decimal('igst_amount', 12, 2)->default(0.00)->after('igst_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn([
                'hsn_code',
                'tax_rate',
                'cgst_rate',
                'cgst_amount',
                'sgst_rate',
                'sgst_amount',
                'igst_rate',
                'igst_amount',
            ]);
        });
    }
};
