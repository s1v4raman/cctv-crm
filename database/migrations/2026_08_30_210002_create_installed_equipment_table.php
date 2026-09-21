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
        Schema::create('installed_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('installation_job_id')->nullable()->constrained('installation_jobs')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('equipment_name');
            $table->string('serial_number')->index();
            $table->string('mac_address')->nullable()->index();
            $table->string('location_tag')->nullable();
            $table->date('installation_date')->nullable();
            $table->date('manufacturer_warranty_expiry')->nullable();
            $table->date('service_warranty_expiry')->nullable();
            $table->string('status', 30)->default('active'); // active, under_repair, replaced, decommissioned
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installed_equipment');
    }
};
