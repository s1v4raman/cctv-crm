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
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
            $table->string('brand')->nullable()->after('category');
            $table->string('model_no')->nullable()->after('brand');
            $table->integer('stock_quantity')->default(0)->after('unit_price');
            $table->integer('min_stock_alert')->default(5)->after('stock_quantity');
            $table->integer('default_warranty_months')->default(24)->after('min_stock_alert');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'brand',
                'model_no',
                'stock_quantity',
                'min_stock_alert',
                'default_warranty_months',
            ]);
        });
    }
};
