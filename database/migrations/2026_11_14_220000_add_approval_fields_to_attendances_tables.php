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
        Schema::table('employee_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_attendances', 'project_id')) {
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            }
            if (!Schema::hasColumn('employee_attendances', 'approval_status')) {
                $table->string('approval_status', 30)->default('approved');
            }
            if (!Schema::hasColumn('employee_attendances', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('employee_attendances', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('employee_attendances', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
        });

        Schema::table('project_worker_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('project_worker_attendances', 'approval_status')) {
                $table->string('approval_status', 30)->default('approved');
            }
            if (!Schema::hasColumn('project_worker_attendances', 'logged_by')) {
                $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('project_worker_attendances', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('project_worker_attendances', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('project_worker_attendances', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            $cols = ['rejection_reason', 'approved_at', 'approved_by', 'approval_status', 'project_id'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('employee_attendances', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('project_worker_attendances', function (Blueprint $table) {
            $cols = ['rejection_reason', 'approved_at', 'approved_by', 'logged_by', 'approval_status'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('project_worker_attendances', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
