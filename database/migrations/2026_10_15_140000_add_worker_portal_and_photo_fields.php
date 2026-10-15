<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add user_id and photo_path to workers table if not present
        Schema::table('workers', function (Blueprint $table) {
            if (!Schema::hasColumn('workers', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('workers', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('phone');
            }
        });

        // 2. Add photo_path, check_in_at, cameras_installed, work_summary to project_worker_attendances
        Schema::table('project_worker_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('project_worker_attendances', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('attendance_type');
            }
            if (!Schema::hasColumn('project_worker_attendances', 'check_in_at')) {
                $table->dateTime('check_in_at')->nullable()->after('photo_path');
            }
            if (!Schema::hasColumn('project_worker_attendances', 'cameras_installed')) {
                $table->integer('cameras_installed')->default(0)->after('cabling_metres');
            }
            if (!Schema::hasColumn('project_worker_attendances', 'work_summary')) {
                $table->text('work_summary')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_worker_attendances', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'check_in_at', 'cameras_installed', 'work_summary']);
        });

        Schema::table('workers', function (Blueprint $table) {
            if (Schema::hasColumn('workers', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('workers', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
        });
    }
};
