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
        Schema::create('amc_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->string('contract_no')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('value', 10, 2);
            $table->enum('frequency', ['monthly', 'quarterly', 'semi_annually', 'annually'])->default('quarterly');
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amc_contracts');
    }
};
