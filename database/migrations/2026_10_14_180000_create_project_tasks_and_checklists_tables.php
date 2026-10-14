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
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('phase_name')->default('General');
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('pending'); // pending, in_progress, completed, blocked
            $table->date('due_date')->nullable();
            $table->decimal('estimated_hours', 6, 2)->nullable();
            $table->decimal('actual_hours', 6, 2)->nullable();
            $table->integer('order_column')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'phase_name']);
        });

        Schema::create('project_task_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->string('item_text');
            $table->boolean('is_completed')->default(false);
            $table->integer('order_column')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_task_id', 'is_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_task_checklists');
        Schema::dropIfExists('project_tasks');
    }
};
