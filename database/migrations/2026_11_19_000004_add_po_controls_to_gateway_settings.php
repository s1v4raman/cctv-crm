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
        Schema::table('gateway_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('gateway_settings', 'po_auto_approve_limit')) {
                $table->decimal('po_auto_approve_limit', 12, 2)->nullable()->after('company_address');
            }
            if (!Schema::hasColumn('gateway_settings', 'po_receiving_tolerance_percent')) {
                $table->decimal('po_receiving_tolerance_percent', 5, 2)->default(0.00)->after('po_auto_approve_limit');
            }
            if (!Schema::hasColumn('gateway_settings', 'po_alert_on_goods_received')) {
                $table->boolean('po_alert_on_goods_received')->default(false)->after('po_receiving_tolerance_percent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gateway_settings', function (Blueprint $table) {
            $table->dropColumn([
                'po_auto_approve_limit',
                'po_receiving_tolerance_percent',
                'po_alert_on_goods_received',
            ]);
        });
    }
};
