<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add product_id and unit_cost to project_materials if not already present
        Schema::table('project_materials', function (Blueprint $table) {
            if (!Schema::hasColumn('project_materials', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('project_id')->constrained('products')->nullOnDelete();
            }
            if (!Schema::hasColumn('project_materials', 'unit_cost')) {
                $table->decimal('unit_cost', 10, 2)->default(0.00)->after('unit');
            }
        });

        // Create task_material_consumptions table
        Schema::create('task_material_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->foreignId('project_material_id')->nullable()->constrained('project_materials')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('item_name');
            $table->decimal('quantity_used', 10, 2);
            $table->string('unit', 50)->default('nos');
            $table->decimal('wastage_quantity', 10, 2)->default(0.00);
            $table->string('drum_or_batch_no')->nullable();
            $table->foreignId('consumed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('consumed_at')->useCurrent();
            $table->boolean('deduct_from_stock')->default(false);
            $table->foreignId('stock_movement_id')->nullable()->constrained('stock_movements')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'project_task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_material_consumptions');

        Schema::table('project_materials', function (Blueprint $table) {
            if (Schema::hasColumn('project_materials', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            if (Schema::hasColumn('project_materials', 'unit_cost')) {
                $table->dropColumn('unit_cost');
            }
        });
    }
};
