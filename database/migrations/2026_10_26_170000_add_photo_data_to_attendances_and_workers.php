<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_worker_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('project_worker_attendances', 'photo_data')) {
                $table->text('photo_data')->nullable()->after('photo_path');
            }
        });

        Schema::table('workers', function (Blueprint $table) {
            if (!Schema::hasColumn('workers', 'photo_data')) {
                $table->text('photo_data')->nullable()->after('photo_path');
            }
        });

        // Backfill existing local attendance photos into photo_data
        try {
            $attendances = DB::table('project_worker_attendances')
                ->whereNotNull('photo_path')
                ->whereNull('photo_data')
                ->get();

            foreach ($attendances as $att) {
                $cleanPath = ltrim(str_replace('public/', '', $att->photo_path), '/');
                if (Storage::disk('public')->exists($cleanPath)) {
                    $contents = Storage::disk('public')->get($cleanPath);
                    if ($contents) {
                        $ext = pathinfo($cleanPath, PATHINFO_EXTENSION) ?: 'jpg';
                        $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                        $base64 = 'data:' . $mime . ';base64,' . base64_encode($contents);
                        DB::table('project_worker_attendances')
                            ->where('id', $att->id)
                            ->update(['photo_data' => $base64]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Non-blocking backfill
        }

        // Backfill existing local worker photos into photo_data
        try {
            $workers = DB::table('workers')
                ->whereNotNull('photo_path')
                ->whereNull('photo_data')
                ->get();

            foreach ($workers as $w) {
                $cleanPath = ltrim(str_replace('public/', '', $w->photo_path), '/');
                if (Storage::disk('public')->exists($cleanPath)) {
                    $contents = Storage::disk('public')->get($cleanPath);
                    if ($contents) {
                        $ext = pathinfo($cleanPath, PATHINFO_EXTENSION) ?: 'jpg';
                        $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                        $base64 = 'data:' . $mime . ';base64,' . base64_encode($contents);
                        DB::table('workers')
                            ->where('id', $w->id)
                            ->update(['photo_data' => $base64]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Non-blocking backfill
        }
    }

    public function down(): void
    {
        Schema::table('project_worker_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('project_worker_attendances', 'photo_data')) {
                $table->dropColumn('photo_data');
            }
        });

        Schema::table('workers', function (Blueprint $table) {
            if (Schema::hasColumn('workers', 'photo_data')) {
                $table->dropColumn('photo_data');
            }
        });
    }
};
