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
        Schema::create('gst_filings', function (Blueprint $table) {
            $table->id();
            $table->string('period', 7)->unique(); // YYYY-MM e.g. 2026-09
            $table->enum('gstr1_status', ['pending', 'reconciled', 'filed'])->default('pending');
            $table->enum('gstr3b_status', ['pending', 'reconciled', 'filed'])->default('pending');
            $table->decimal('total_turnover', 14, 2)->default(0.00);
            $table->decimal('taxable_turnover', 14, 2)->default(0.00);
            $table->decimal('output_tax', 14, 2)->default(0.00);
            $table->decimal('input_tax_credit', 14, 2)->default(0.00);
            $table->decimal('net_tax_payable', 14, 2)->default(0.00);
            $table->decimal('tax_paid', 14, 2)->default(0.00);
            $table->string('challan_no', 50)->nullable();
            $table->string('cin_number', 50)->nullable(); // Challan Identification Number
            $table->date('filing_date')->nullable();
            $table->string('payment_mode')->nullable()->default('online_portal'); // online_portal, neft_rtgs, over_counter
            $table->text('notes')->nullable();
            $table->foreignId('filed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gst_filings');
    }
};
