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
        Schema::create('gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->string('active_sms_gateway')->default('log'); // log, twilio, msg91, custom_webhook
            
            // Twilio Configuration
            $table->string('twilio_account_sid')->nullable();
            $table->string('twilio_auth_token')->nullable();
            $table->string('twilio_from_number')->nullable();

            // MSG91 Configuration
            $table->string('msg91_auth_key')->nullable();
            $table->string('msg91_sender_id')->nullable();
            $table->string('msg91_flow_id')->nullable();
            $table->string('msg91_dlt_te_id')->nullable();

            // Custom Webhook Configuration
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->string('webhook_method')->default('POST'); // POST, GET, PUT
            $table->text('webhook_headers')->nullable(); // JSON headers
            $table->text('webhook_payload_template')->nullable(); // Custom JSON template

            // System Notification Toggles
            $table->boolean('ticket_auto_sms_enabled')->default(true);
            $table->boolean('ticket_auto_whatsapp_enabled')->default(true);
            $table->boolean('otp_sms_enabled')->default(true);
            $table->boolean('otp_whatsapp_enabled')->default(true);
            $table->integer('otp_expiry_minutes')->default(10);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gateway_settings');
    }
};
