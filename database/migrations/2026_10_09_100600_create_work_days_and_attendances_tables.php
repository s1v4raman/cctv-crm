<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->date('work_date');
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['project_id', 'work_date']);
        });

        Schema::create('project_worker_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_day_id')->constrained('work_days')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->string('attendance_type', 30)->default('full_day');
            $table->decimal('daily_rate_snapshot', 10, 2)->nullable();
            $table->decimal('rate_applied', 10, 2)->nullable();
            $table->decimal('cabling_metres', 10, 2)->default(0.00);
            $table->decimal('cabling_rate', 8, 2)->default(0.00);
            $table->decimal('cabling_amount', 10, 2)->default(0.00);
            $table->string('extra_type', 30)->default('none');
            $table->decimal('metres', 10, 2)->default(0.00);
            $table->decimal('extra_amount', 10, 2)->default(0.00);
            $table->string('extra_description')->nullable();
            $table->string('extra_reason')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->date('paid_week_start')->nullable();
            $table->unsignedBigInteger('wage_payment_id')->nullable();
            $table->foreignId('unlocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('unlocked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['work_day_id', 'worker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_worker_attendances');
        Schema::dropIfExists('work_days');
    }
};
