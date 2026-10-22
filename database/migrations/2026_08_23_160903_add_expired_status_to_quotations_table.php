<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'pgsql' || config('database.default') === 'pgsql') {
            DB::statement("ALTER TABLE quotations DROP CONSTRAINT IF EXISTS quotations_status_check");
            DB::statement("ALTER TABLE quotations ADD CONSTRAINT quotations_status_check CHECK (status::text IN ('draft', 'sent', 'accepted', 'rejected', 'expired'))");
            return;
        }

        if ($driver === 'mysql') {
            Schema::table('quotations', function (Blueprint $table) {
                DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft','sent','accepted','rejected','expired') DEFAULT 'draft'");
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'pgsql' || config('database.default') === 'pgsql') {
            DB::statement("ALTER TABLE quotations DROP CONSTRAINT IF EXISTS quotations_status_check");
            DB::statement("ALTER TABLE quotations ADD CONSTRAINT quotations_status_check CHECK (status::text IN ('draft', 'sent', 'accepted', 'rejected'))");
            return;
        }

        if ($driver === 'mysql') {
            Schema::table('quotations', function (Blueprint $table) {
                DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft','sent','accepted','rejected') DEFAULT 'draft'");
            });
        }
    }
};

