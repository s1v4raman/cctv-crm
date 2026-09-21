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
        Schema::create('service_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('amc_contract_id')->nullable()->constrained('amc_contracts')->nullOnDelete();
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('title');
            $table->enum('issue_type', [
                'camera_offline',
                'dvr_nvr_beep',
                'blurry_feed',
                'recording_failure',
                'power_supply_issue',
                'network_issue',
                'cable_damaged',
                'ptz_control_issue',
                'other'
            ])->default('other');
            
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'assigned', 'in_progress', 'resolved', 'closed', 'cancelled'])->default('open');
            $table->text('description');
            
            $table->date('scheduled_date')->nullable();
            $table->text('troubleshooting_notes')->nullable();
            $table->text('parts_replaced')->nullable();
            $table->text('resolution_notes')->nullable();
            
            $table->enum('billing_type', ['warranty_amc', 'billable', 'free_courtesy'])->default('warranty_amc');
            $table->decimal('cost', 10, 2)->nullable();
            
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_tickets');
    }
};
