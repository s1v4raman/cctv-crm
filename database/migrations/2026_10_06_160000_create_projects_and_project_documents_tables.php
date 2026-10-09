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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('title');
            $table->string('company_name')->nullable()->index();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('site_address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'incompleted', 'on_hold', 'cancelled'])->default('in_progress')->index();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->decimal('budget', 12, 2)->nullable()->default(0.00);
            $table->decimal('actual_cost', 12, 2)->nullable()->default(0.00);
            $table->date('start_date')->nullable();
            $table->date('deadline')->nullable();
            $table->date('completed_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('file_type', 50)->default('pdf');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_documents');
        Schema::dropIfExists('projects');
    }
};
