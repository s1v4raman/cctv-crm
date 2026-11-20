<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('suppliers') || !Schema::hasTable('vendor_supplier_mappings')) {
            return;
        }

        DB::transaction(function () {
            // 1. Backfill name_key for existing suppliers if missing
            $existingSuppliers = DB::table('suppliers')->get();
            foreach ($existingSuppliers as $sup) {
                if (empty($sup->name_key)) {
                    $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $sup->name ?? '')));
                    // Ensure uniqueness
                    $candidate = $normalized;
                    $counter = 1;
                    while (DB::table('suppliers')->where('name_key', $candidate)->where('id', '!=', $sup->id)->exists()) {
                        $candidate = "{$normalized}-{$counter}";
                        $counter++;
                    }

                    DB::table('suppliers')->where('id', $sup->id)->update([
                        'name_key' => $candidate,
                        'type'     => $sup->type ?: 'distributor',
                        'status'   => $sup->status ?: 'approved',
                    ]);
                }
            }

            // 2. Check if vendors table exists
            if (!Schema::hasTable('vendors')) {
                return;
            }

            $vendors = DB::table('vendors')->get();

            foreach ($vendors as $vendor) {
                $vendorKey = strtolower(trim($vendor->name_key ?: $vendor->name));
                $cleanName = trim(preg_replace('/\s+/', ' ', $vendor->name ?? ''));

                // Check if already mapped
                $alreadyMapped = DB::table('vendor_supplier_mappings')->where('vendor_id', $vendor->id)->first();
                if ($alreadyMapped) {
                    continue;
                }

                // Check if an existing supplier matches either by name_key or company_name
                $matchedSupplier = DB::table('suppliers')
                    ->where(function ($q) use ($vendorKey, $cleanName) {
                        $q->where('name_key', $vendorKey)
                          ->orWhereRaw('LOWER(name) = ?', [strtolower($cleanName)])
                          ->orWhereRaw('LOWER(company_name) LIKE ?', ['%' . strtolower($cleanName) . '%']);
                    })
                    ->first();

                if ($matchedSupplier) {
                    // Map to existing supplier
                    DB::table('vendor_supplier_mappings')->insert([
                        'vendor_id'   => $vendor->id,
                        'supplier_id' => $matchedSupplier->id,
                        'notes'       => 'matched_existing_supplier',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $targetSupplierId = $matchedSupplier->id;
                } else {
                    // Create new supplier of type 'local_shop'
                    $supplierKey = $vendorKey;
                    $counter = 1;
                    while (DB::table('suppliers')->where('name_key', $supplierKey)->exists()) {
                        $supplierKey = "{$vendorKey}-{$counter}";
                        $counter++;
                    }

                    $newSupplierId = DB::table('suppliers')->insertGetId([
                        'name'          => $cleanName,
                        'name_key'      => $supplierKey,
                        'company_name'  => $cleanName,
                        'phone'         => $vendor->phone,
                        'address'       => $vendor->address,
                        'type'          => 'local_shop',
                        'status'        => $vendor->status ?? 'pending',
                        'created_by'    => $vendor->created_by,
                        'is_active'     => true,
                        'created_at'    => $vendor->created_at ?: now(),
                        'updated_at'    => $vendor->updated_at ?: now(),
                    ]);

                    DB::table('vendor_supplier_mappings')->insert([
                        'vendor_id'   => $vendor->id,
                        'supplier_id' => $newSupplierId,
                        'notes'       => 'migrated_from_vendors_table',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $targetSupplierId = $newSupplierId;
                }

                // Update project_materials pointing to this vendor
                if (Schema::hasTable('project_materials') && Schema::hasColumn('project_materials', 'supplier_id')) {
                    DB::table('project_materials')
                        ->where('vendor_id', $vendor->id)
                        ->update(['supplier_id' => $targetSupplierId]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('vendor_supplier_mappings')) {
            return;
        }

        DB::transaction(function () {
            $createdMappings = DB::table('vendor_supplier_mappings')
                ->where('notes', 'migrated_from_vendors_table')
                ->get();

            foreach ($createdMappings as $mapping) {
                // Remove newly created suppliers
                DB::table('suppliers')->where('id', $mapping->supplier_id)->delete();
            }

            // Clear mappings
            DB::table('vendor_supplier_mappings')->truncate();

            if (Schema::hasTable('project_materials') && Schema::hasColumn('project_materials', 'supplier_id')) {
                DB::table('project_materials')->update(['supplier_id' => null]);
            }
        });
    }
};
