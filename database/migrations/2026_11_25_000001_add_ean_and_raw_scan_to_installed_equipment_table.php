<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installed_equipment', function (Blueprint $table) {
            if (!Schema::hasColumn('installed_equipment', 'ean')) {
                $table->string('ean', 20)->nullable()->after('mac_address')->index();
            }
            if (!Schema::hasColumn('installed_equipment', 'raw_scan')) {
                $table->text('raw_scan')->nullable()->after('ean');
            }
        });
    }

    public function down(): void
    {
        Schema::table('installed_equipment', function (Blueprint $table) {
            if (Schema::hasColumn('installed_equipment', 'raw_scan')) {
                $table->dropColumn('raw_scan');
            }
            if (Schema::hasColumn('installed_equipment', 'ean')) {
                $table->dropColumn('ean');
            }
        });
    }
};
