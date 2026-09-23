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
        Schema::table('installation_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('installation_jobs', 'labor_hours_logged')) {
                $table->decimal('labor_hours_logged', 6, 2)->nullable()->after('status');
            }
            if (!Schema::hasColumn('installation_jobs', 'custom_hourly_rate')) {
                $table->decimal('custom_hourly_rate', 8, 2)->nullable()->after('labor_hours_logged');
            }
            if (!Schema::hasColumn('installation_jobs', 'other_direct_costs')) {
                $table->decimal('other_direct_costs', 10, 2)->default(0.00)->after('custom_hourly_rate');
            }
            if (!Schema::hasColumn('installation_jobs', 'costing_notes')) {
                $table->text('costing_notes')->nullable()->after('other_direct_costs');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installation_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'labor_hours_logged',
                'custom_hourly_rate',
                'other_direct_costs',
                'costing_notes',
            ]);
        });
    }
};
