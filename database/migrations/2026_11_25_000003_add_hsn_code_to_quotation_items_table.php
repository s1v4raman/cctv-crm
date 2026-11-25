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
        if (Schema::hasTable('quotation_items') && !Schema::hasColumn('quotation_items', 'hsn_code')) {
            Schema::table('quotation_items', function (Blueprint $table) {
                $table->string('hsn_code', 8)->nullable()->after('unit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('quotation_items') && Schema::hasColumn('quotation_items', 'hsn_code')) {
            Schema::table('quotation_items', function (Blueprint $table) {
                $table->dropColumn('hsn_code');
            });
        }
    }
};
