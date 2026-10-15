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
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('status');
            $table->text('blocker_reason')->nullable()->after('description');
            $table->timestamp('blocked_at')->nullable()->after('completed_by');
            $table->foreignId('blocked_by')->nullable()->after('blocked_at')->constrained('users')->nullOnDelete();
            $table->foreignId('depends_on_task_id')->nullable()->after('blocked_by')->constrained('project_tasks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign(['depends_on_task_id']);
            $table->dropForeign(['blocked_by']);
            $table->dropColumn([
                'start_date',
                'blocker_reason',
                'blocked_at',
                'blocked_by',
                'depends_on_task_id',
            ]);
        });
    }
};
