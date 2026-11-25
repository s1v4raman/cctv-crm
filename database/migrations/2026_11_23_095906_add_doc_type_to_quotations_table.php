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
        if (!Schema::hasColumn('quotations', 'doc_type')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->string('doc_type', 30)->default('quotation')->after('pricing_mode');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('quotations', 'doc_type')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropColumn('doc_type');
            });
        }
    }
};
