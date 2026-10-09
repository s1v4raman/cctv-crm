<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('device_name')->default('Device');
            $table->string('device_type', 100)->default('ip_camera');
            $table->string('ip_address', 45);
            $table->string('subnet_mask', 45)->nullable()->default('255.255.255.0');
            $table->string('gateway', 45)->nullable();
            $table->string('port', 15)->nullable()->default('80');
            $table->unsignedInteger('web_port')->nullable()->default(80);
            $table->unsignedInteger('rtsp_port')->nullable()->default(554);
            $table->unsignedInteger('server_port')->nullable()->default(8000);
            $table->string('mac_address', 50)->nullable();
            $table->string('make_model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('username')->nullable()->default('admin');
            $table->text('password')->nullable(); // encrypted with Laravel cast
            $table->string('location')->nullable();
            $table->string('fixed_location')->nullable();
            $table->string('status', 30)->nullable()->default('configured');
            $table->string('nvr_channel', 20)->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('ip_device_reveal_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ip_device_id')->constrained('ip_devices')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('revealed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_device_reveal_logs');
        Schema::dropIfExists('ip_devices');
    }
};
