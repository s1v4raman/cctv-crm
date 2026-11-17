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
        Schema::table('expense_claims', function (Blueprint $table) {
            if (!Schema::hasColumn('expense_claims', 'site_id')) {
                $table->foreignId('site_id')->nullable()->after('installation_job_id')->constrained('sites')->nullOnDelete();
            }
            if (!Schema::hasColumn('expense_claims', 'project_id')) {
                $table->foreignId('project_id')->nullable()->after('site_id')->constrained('projects')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_claims', function (Blueprint $table) {
            if (Schema::hasColumn('expense_claims', 'project_id')) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            }
            if (Schema::hasColumn('expense_claims', 'site_id')) {
                $table->dropForeign(['site_id']);
                $table->dropColumn('site_id');
            }
        });
    }
};
