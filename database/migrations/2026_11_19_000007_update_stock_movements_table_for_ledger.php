<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('movement_type', 50)->default('receipt')->after('product_id');
            $table->integer('qty')->default(0)->after('movement_type');
            $table->decimal('unit_cost', 12, 2)->nullable()->after('qty');
            $table->foreignId('project_id')->nullable()->after('reference_id')->constrained('projects')->nullOnDelete();
            $table->text('note')->nullable()->after('project_id');
            $table->foreignId('created_by')->nullable()->after('note')->constrained('users')->nullOnDelete();
        });

        // Backfill existing movements
        DB::statement("
            UPDATE stock_movements 
            SET movement_type = CASE 
                    WHEN type = 'in' THEN 'receipt' 
                    WHEN type = 'out' THEN 'issue' 
                    WHEN type = 'job_installation' THEN 'issue'
                    WHEN type = 'return' THEN 'return_to_vendor'
                    WHEN type = 'adjustment' THEN 'adjustment'
                    ELSE 'receipt'
                END,
                qty = CASE 
                    WHEN type IN ('out', 'job_installation') THEN -ABS(quantity) 
                    ELSE quantity 
                END,
                note = notes,
                created_by = user_id
        ");
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['movement_type', 'qty', 'unit_cost', 'project_id', 'note', 'created_by']);
        });
    }
};
