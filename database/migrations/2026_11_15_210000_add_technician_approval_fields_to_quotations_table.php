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
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('lead_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('quotations', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('quotations', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql' || config('database.default') === 'pgsql') {
            DB::statement("ALTER TABLE quotations DROP CONSTRAINT IF EXISTS quotations_status_check");
            DB::statement("ALTER TABLE quotations ADD CONSTRAINT quotations_status_check CHECK (status::text IN ('draft', 'pending_approval', 'approved', 'sent', 'accepted', 'rejected', 'expired'))");
        } elseif ($driver === 'mysql') {
            Schema::table('quotations', function (Blueprint $table) {
                DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft','pending_approval','approved','sent','accepted','rejected','expired') DEFAULT 'draft'");
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql' || config('database.default') === 'pgsql') {
            DB::statement("ALTER TABLE quotations DROP CONSTRAINT IF EXISTS quotations_status_check");
            DB::statement("ALTER TABLE quotations ADD CONSTRAINT quotations_status_check CHECK (status::text IN ('draft', 'sent', 'accepted', 'rejected', 'expired'))");
        }

        Schema::table('quotations', function (Blueprint $table) {
            if (Schema::hasColumn('quotations', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('quotations', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
            if (Schema::hasColumn('quotations', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};
