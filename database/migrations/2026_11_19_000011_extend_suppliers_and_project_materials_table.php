<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Extend suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'name_key')) {
                $table->string('name_key')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('suppliers', 'type')) {
                $table->string('type', 50)->default('distributor')->after('payment_terms'); // manufacturer, distributor, local_shop, service_provider
            }
            if (!Schema::hasColumn('suppliers', 'status')) {
                $table->string('status', 30)->default('approved')->after('type'); // pending, approved
            }
            if (!Schema::hasColumn('suppliers', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
        });

        // 2. Create mapping table vendor_supplier_mappings for audit trail
        if (!Schema::hasTable('vendor_supplier_mappings')) {
            Schema::create('vendor_supplier_mappings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vendor_id')->index();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->string('notes')->nullable();
                $table->timestamps();

                $table->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
            });
        }

        // 3. Add supplier_id to project_materials table
        if (Schema::hasTable('project_materials') && !Schema::hasColumn('project_materials', 'supplier_id')) {
            Schema::table('project_materials', function (Blueprint $table) {
                $table->foreignId('supplier_id')->nullable()->after('vendor_id')->constrained('suppliers')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project_materials') && Schema::hasColumn('project_materials', 'supplier_id')) {
            Schema::table('project_materials', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            });
        }

        Schema::dropIfExists('vendor_supplier_mappings');

        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('suppliers', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('suppliers', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('suppliers', 'name_key')) {
                $table->dropUnique(['name_key']);
                $table->dropColumn('name_key');
            }
        });
    }
};
