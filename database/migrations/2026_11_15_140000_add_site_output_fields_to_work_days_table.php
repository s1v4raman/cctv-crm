<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_days', function (Blueprint $table) {
            if (!Schema::hasColumn('work_days', 'total_cabling_metres')) {
                $table->decimal('total_cabling_metres', 10, 2)->default(0)->after('notes');
            }
            if (!Schema::hasColumn('work_days', 'total_cameras_installed')) {
                $table->integer('total_cameras_installed')->default(0)->after('total_cabling_metres');
            }
        });

        // Backfill existing work days from their worker attendance records
        try {
            $workDays = DB::table('work_days')->select('id')->get();
            foreach ($workDays as $wd) {
                $sumCabling = (float) DB::table('project_worker_attendances')
                    ->where('work_day_id', $wd->id)
                    ->sum('cabling_metres');

                $sumCameras = (int) DB::table('project_worker_attendances')
                    ->where('work_day_id', $wd->id)
                    ->sum('cameras_installed');

                if ($sumCabling > 0 || $sumCameras > 0) {
                    DB::table('work_days')->where('id', $wd->id)->update([
                        'total_cabling_metres' => $sumCabling,
                        'total_cameras_installed' => $sumCameras,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore if tables are empty
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_days', function (Blueprint $table) {
            if (Schema::hasColumn('work_days', 'total_cameras_installed')) {
                $table->dropColumn('total_cameras_installed');
            }
            if (Schema::hasColumn('work_days', 'total_cabling_metres')) {
                $table->dropColumn('total_cabling_metres');
            }
        });
    }
};
