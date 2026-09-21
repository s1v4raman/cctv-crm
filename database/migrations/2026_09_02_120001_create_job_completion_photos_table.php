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
        Schema::create('job_completion_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_completion_report_id')->constrained('job_completion_reports')->cascadeOnDelete();
            $table->string('photo_type')->default('general'); // rack_setup, camera_view, dvr_nvr_screen, handover, general
            $table->string('filename');
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_completion_photos');
    }
};
