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
            $table->string('shift')->nullable()->default('Day Shift')->after('status');
            $table->string('location')->nullable()->after('shift');
            $table->timestamp('accepted_at')->nullable()->after('completed_by');
            $table->timestamp('rejected_at')->nullable()->after('accepted_at');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('rejected_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign(['rejected_by']);
            $table->dropColumn([
                'shift',
                'location',
                'accepted_at',
                'rejected_at',
                'rejected_by',
                'rejection_reason',
            ]);
        });
    }
};
