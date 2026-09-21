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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('period_type', ['monthly', 'weekly', 'daily'])->default('monthly');
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('working_days')->default(26);
            $table->decimal('present_days', 4, 1)->default(0.0);
            $table->integer('half_days')->default(0);
            $table->decimal('leave_days', 4, 1)->default(0.0);
            $table->decimal('absent_days', 4, 1)->default(0.0);
            $table->decimal('overtime_hours', 5, 2)->default(0.00);
            $table->decimal('basic_pay', 12, 2)->default(0.00);
            $table->decimal('overtime_pay', 10, 2)->default(0.00);
            $table->decimal('allowances', 10, 2)->default(0.00);
            $table->decimal('deductions', 10, 2)->default(0.00);
            $table->decimal('net_salary', 12, 2)->default(0.00);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->date('payment_date')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
