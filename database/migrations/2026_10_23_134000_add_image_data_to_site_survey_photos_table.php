<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_survey_photos', function (Blueprint $table) {
            if (!Schema::hasColumn('site_survey_photos', 'image_data')) {
                $table->longText('image_data')->nullable();
            }
            if (!Schema::hasColumn('site_survey_photos', 'mime_type')) {
                $table->string('mime_type', 100)->nullable()->default('image/jpeg');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_survey_photos', function (Blueprint $table) {
            if (Schema::hasColumn('site_survey_photos', 'image_data')) {
                $table->dropColumn('image_data');
            }
            if (Schema::hasColumn('site_survey_photos', 'mime_type')) {
                $table->dropColumn('mime_type');
            }
        });
    }
};
