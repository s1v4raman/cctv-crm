<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_payments', function (Blueprint $table) {
            $table->foreignId('vendor_bill_id')->nullable()->after('purchase_order_id')->constrained('vendor_bills')->nullOnDelete();
            $table->string('kind', 30)->default('against_bill')->after('amount'); // advance, against_bill
        });

        Schema::create('vendor_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_payment_id')->constrained('vendor_payments')->cascadeOnDelete();
            $table->foreignId('vendor_bill_id')->constrained('vendor_bills')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_payment_allocations');
        Schema::table('vendor_payments', function (Blueprint $table) {
            $table->dropForeign(['vendor_bill_id']);
            $table->dropColumn(['vendor_bill_id', 'kind']);
        });
    }
};
