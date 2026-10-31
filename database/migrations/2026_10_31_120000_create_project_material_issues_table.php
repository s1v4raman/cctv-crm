<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Material handed from the storeroom to a project site, and what came back.
     */
    public function up(): void
    {
        Schema::create('project_material_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('type', 20); // issue (storeroom to site) or return (site to storeroom)
            $table->integer('quantity');
            $table->string('unit', 50)->nullable();
            $table->foreignId('worker_id')->nullable()->constrained('workers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('issued_on');
            $table->string('drum_or_batch_no', 100)->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('stock_movement_id')->nullable()->constrained('stock_movements')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_material_issues');
    }
};
