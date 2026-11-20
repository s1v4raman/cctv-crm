<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number', 50)->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->date('received_date');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('delivery_note_no', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->integer('qty_received')->default(0);
            $table->integer('qty_rejected')->default(0);
            $table->string('reject_reason', 255)->nullable();
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('goods_receipt_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_item_id')->constrained('goods_receipt_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('serial_no', 100);
            $table->string('mac_address', 100)->nullable();
            $table->date('warranty_until')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'serial_no'], 'uniq_product_serial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_serials');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
    }
};
