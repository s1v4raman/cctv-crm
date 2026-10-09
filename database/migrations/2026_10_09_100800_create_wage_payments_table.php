<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wage_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->enum('payment_type', ['advance', 'wage'])->default('wage');
            $table->date('week_start_date')->nullable();
            $table->date('week_end_date')->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->enum('payment_mode', ['cash', 'upi', 'bank'])->default('cash');
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wage_payments');
    }
};
