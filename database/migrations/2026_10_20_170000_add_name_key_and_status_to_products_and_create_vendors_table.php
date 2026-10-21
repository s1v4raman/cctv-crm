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
        // 1. Add name_key, status, and created_by to products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'name_key')) {
                $table->string('name_key')->nullable()->after('name');
            }
            if (!Schema::hasColumn('products', 'status')) {
                $table->string('status')->default('approved')->after('name_key');
            }
            if (!Schema::hasColumn('products', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
        });

        // 2. Backfill existing products with normalized name_key and 'approved' status
        $existingProducts = DB::table('products')->get();
        $usedKeys = [];
        foreach ($existingProducts as $p) {
            $baseKey = strtolower(trim(preg_replace('/\s+/', ' ', $p->name ?? 'product')));
            if ($baseKey === '') {
                $baseKey = 'product-' . $p->id;
            }
            $uniqueKey = $baseKey;
            $counter = 1;
            while (in_array($uniqueKey, $usedKeys, true)) {
                $uniqueKey = $baseKey . '-' . $counter;
                $counter++;
            }
            $usedKeys[] = $uniqueKey;

            DB::table('products')->where('id', $p->id)->update([
                'name_key' => $uniqueKey,
                'status' => $p->status ?: 'approved',
            ]);
        }

        // Add unique index on products.name_key if not already indexed
        Schema::table('products', function (Blueprint $table) {
            $table->unique('name_key');
        });

        // 3. Create vendors table
        if (!Schema::hasTable('vendors')) {
            Schema::create('vendors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_key')->unique();
                $table->string('status')->default('pending'); // 'pending', 'approved'
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->timestamps();
            });
        }

        // 4. Add vendor_id to project_materials table
        if (Schema::hasTable('project_materials') && !Schema::hasColumn('project_materials', 'vendor_id')) {
            Schema::table('project_materials', function (Blueprint $table) {
                $table->foreignId('vendor_id')->nullable()->after('product_id')->constrained('vendors')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project_materials') && Schema::hasColumn('project_materials', 'vendor_id')) {
            Schema::table('project_materials', function (Blueprint $table) {
                $table->dropForeign(['vendor_id']);
                $table->dropColumn('vendor_id');
            });
        }

        Schema::dropIfExists('vendors');

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'name_key')) {
                    $table->dropUnique(['name_key']);
                    $table->dropColumn('name_key');
                }
                if (Schema::hasColumn('products', 'created_by')) {
                    $table->dropForeign(['created_by']);
                    $table->dropColumn('created_by');
                }
                if (Schema::hasColumn('products', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};
