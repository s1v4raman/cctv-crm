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
        if (Schema::hasTable('project_materials') && !Schema::hasColumn('project_materials', 'vendor_id')) {
            Schema::table('project_materials', function (Blueprint $table) {
                if (Schema::hasTable('vendors')) {
                    $table->foreignId('vendor_id')->nullable()->after('product_id')->constrained('vendors')->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('vendor_id')->nullable()->after('product_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project_materials') && Schema::hasColumn('project_materials', 'vendor_id')) {
            Schema::table('project_materials', function (Blueprint $table) {
                $table->dropColumn('vendor_id');
            });
        }
    }
};
