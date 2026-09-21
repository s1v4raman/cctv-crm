<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_survey_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_survey_id')->constrained('site_surveys')->cascadeOnDelete();
            $table->string('filename');
            $table->string('original_name');
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_survey_photos');
    }
};
