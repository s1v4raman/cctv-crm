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
        Schema::create('job_completion_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_no')->unique();
            $table->foreignId('installation_job_id')->nullable()->constrained('installation_jobs')->nullOnDelete();
            $table->foreignId('service_ticket_id')->nullable()->constrained('service_tickets')->nullOnDelete();
            $table->foreignId('amc_visit_id')->nullable()->constrained('amc_visits')->nullOnDelete();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('users');
            
            $table->dateTime('completion_date');
            $table->string('signer_name');
            $table->string('signer_designation')->nullable();
            $table->string('signer_phone')->nullable();
            $table->tinyInteger('customer_rating')->nullable()->default(5);
            $table->text('customer_feedback')->nullable();
            
            $table->longText('customer_signature'); // Base64 PNG signature string
            $table->longText('technician_signature')->nullable();

            // Quality Handover Checklist
            $table->boolean('all_cameras_positioned')->default(true);
            $table->boolean('recording_configured')->default(true);
            $table->boolean('remote_mobile_app_setup')->default(true);
            $table->boolean('power_backup_tested')->default(true);
            $table->boolean('cables_dressed_and_trunked')->default(true);
            $table->boolean('client_training_completed')->default(true);
            $table->boolean('work_area_cleaned')->default(true);
            $table->boolean('warranty_card_handed')->default(true);

            $table->text('work_summary')->nullable();
            $table->text('equipment_tested_notes')->nullable();
            $table->enum('status', ['signed', 'approved', 'pending_review'])->default('signed');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_completion_reports');
    }
};
