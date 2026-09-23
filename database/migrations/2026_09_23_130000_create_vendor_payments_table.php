<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add due_date to purchase_orders if not present
        if (Schema::hasTable('purchase_orders') && !Schema::hasColumn('purchase_orders', 'due_date')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->date('due_date')->nullable()->after('expected_delivery_date');
            });
        }

        // Create vendor_payments table
        if (!Schema::hasTable('vendor_payments')) {
            Schema::create('vendor_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
                $table->string('payment_reference')->unique();
                $table->date('payment_date');
                $table->decimal('amount', 12, 2);
                $table->string('payment_method')->default('neft_rtgs'); // neft_rtgs, cheque, upi, bank_transfer, cash
                $table->string('transaction_reference')->nullable(); // UTR / Cheque #
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['supplier_id', 'payment_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_payments');

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'due_date')) {
                $table->dropColumn('due_date');
            }
        });
    }
};
