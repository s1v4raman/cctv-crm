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
        // In PostgreSQL, enum fields create a check constraint like "purchase_orders_status_check"
        // We drop this constraint and ensure status is varchar(50)
        try {
            DB::statement("ALTER TABLE purchase_orders DROP CONSTRAINT IF EXISTS purchase_orders_status_check");
        } catch (\Throwable $e) {
            // Ignore if constraint does not exist
        }

        try {
            DB::statement("ALTER TABLE purchase_orders ALTER COLUMN status TYPE VARCHAR(50)");
            DB::statement("ALTER TABLE purchase_orders ALTER COLUMN status SET DEFAULT 'draft'");
        } catch (\Throwable $e) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->string('status', 50)->default('draft')->change();
            });
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_orders', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('purchase_orders', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_orders', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('purchase_orders', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });

        // Backfill existing 'ordered' rows to 'sent'
        DB::table('purchase_orders')
            ->where('status', 'ordered')
            ->update(['status' => 'sent']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            if (Schema::hasColumn('purchase_orders', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
            if (Schema::hasColumn('purchase_orders', 'rejected_by')) {
                $table->dropConstrainedForeignId('rejected_by');
            }
            if (Schema::hasColumn('purchase_orders', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('purchase_orders', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
        });
    }
};
