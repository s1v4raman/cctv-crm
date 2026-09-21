<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('surveyed_by')->constrained('users')->cascadeOnDelete();
            $table->date('survey_date');
            $table->string('site_address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->unsignedInteger('camera_count_recommended')->nullable();
            $table->string('dvr_location')->nullable();
            $table->decimal('cable_length_estimate', 8, 2)->nullable();
            $table->text('power_availability')->nullable();
            $table->text('visit_notes')->nullable();
            $table->text('challenges')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_surveys');
    }
};
