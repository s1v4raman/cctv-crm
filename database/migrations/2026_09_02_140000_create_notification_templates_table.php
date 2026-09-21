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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique(); // quotation_sent, job_scheduled, amc_visit_reminder, amc_expiry_alert, etc.
            $table->string('title');
            $table->string('category')->default('general'); // quotations, jobs, amc, service, invoices, rma
            $table->text('whatsapp_template');
            $table->text('sms_template')->nullable();
            $table->string('email_subject');
            $table->text('email_body');
            $table->json('available_variables')->nullable();
            $table->boolean('is_whatsapp_enabled')->default(true);
            $table->boolean('is_sms_enabled')->default(true);
            $table->boolean('is_email_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
