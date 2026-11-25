<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'ean')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('ean', 20)->nullable()->unique()->after('model_no');
            });
        }

        if (!Schema::hasTable('product_aliases')) {
            Schema::create('product_aliases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('alias_type', 24); // ean | model_code | mpn | label_text
                $table->string('value');
                $table->string('normalized_value');
                $table->string('source', 24)->default('manual'); // manual | scan_learned
                $table->string('status', 24)->default('approved'); // approved | pending
                $table->timestamps();

                $table->unique(['alias_type', 'normalized_value'], 'product_aliases_type_norm_unique');
                $table->index(['status', 'alias_type'], 'product_aliases_status_type_idx');
            });
        }

        if (Schema::hasTable('installed_equipment')) {
            Schema::table('installed_equipment', function (Blueprint $table) {
                if (!Schema::hasColumn('installed_equipment', 'ean')) {
                    $table->string('ean', 20)->nullable()->after('mac_address')->index();
                }
                if (!Schema::hasColumn('installed_equipment', 'scan_source')) {
                    $table->string('scan_source', 24)->nullable()->after('ean');
                }
                if (!Schema::hasColumn('installed_equipment', 'raw_scan')) {
                    if (DB::getDriverName() === 'pgsql') {
                        $table->jsonb('raw_scan')->nullable()->after('scan_source');
                    } else {
                        $table->json('raw_scan')->nullable()->after('scan_source');
                    }
                }
            });

            $driver = DB::getDriverName();
            if (in_array($driver, ['pgsql', 'sqlite'], true)) {
                DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS installed_equipment_product_serial_unique ON installed_equipment (product_id, serial_number) WHERE product_id IS NOT NULL');
                DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS installed_equipment_mac_unique ON installed_equipment (mac_address) WHERE mac_address IS NOT NULL');
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement('DROP INDEX IF EXISTS installed_equipment_product_serial_unique');
            DB::statement('DROP INDEX IF EXISTS installed_equipment_mac_unique');
        }

        if (Schema::hasTable('installed_equipment') && Schema::hasColumn('installed_equipment', 'scan_source')) {
            Schema::table('installed_equipment', function (Blueprint $table) {
                $table->dropColumn('scan_source');
            });
        }

        Schema::dropIfExists('product_aliases');

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'ean')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique(['ean']);
                $table->dropColumn('ean');
            });
        }
    }
};
