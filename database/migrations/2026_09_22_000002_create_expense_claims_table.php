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
        Schema::create('expense_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_no')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('installation_job_id')->nullable()->constrained('installation_jobs')->nullOnDelete();
            $table->foreignId('service_ticket_id')->nullable()->constrained('service_tickets')->nullOnDelete();
            $table->enum('expense_category', [
                'fuel_travel',
                'hardware_tools',
                'food_lodging',
                'toll_parking',
                'emergency_materials',
                'other',
            ])->default('fuel_travel');
            $table->date('expense_date');
            $table->decimal('amount', 10, 2);
            $table->decimal('travel_distance_km', 8, 2)->nullable();
            $table->string('travel_from')->nullable();
            $table->string('travel_to')->nullable();
            $table->decimal('rate_per_km', 6, 2)->nullable();
            $table->text('description');
            $table->string('receipt_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid', 'cancelled'])->default('pending');
            $table->foreignId('actioned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('actioned_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->enum('payment_method', ['cash', 'upi', 'bank_transfer', 'payroll_addition'])->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['expense_date', 'expense_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_claims');
    }
};
