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
        // 1. Products: HSN / SAC Code and default Tax Rate
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'hsn_code')) {
                $table->string('hsn_code', 20)->nullable()->default('8525')->after('model_no');
            }
            if (!Schema::hasColumn('products', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(18.00)->after('unit_price');
            }
        });

        // 2. Leads / Customers: GSTIN, Legal Name, State, State Code
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'gstin')) {
                $table->string('gstin', 15)->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('leads', 'company_legal_name')) {
                $table->string('company_legal_name')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('leads', 'state')) {
                $table->string('state', 100)->nullable()->default('Tamil Nadu')->after('site_address');
            }
            if (!Schema::hasColumn('leads', 'state_code')) {
                $table->string('state_code', 2)->nullable()->default('33')->after('state');
            }
        });

        // 3. Invoices: Tax heads (CGST, SGST, IGST), Place of Supply, B2B flag
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'place_of_supply')) {
                $table->string('place_of_supply', 100)->nullable()->default('Tamil Nadu')->after('tax_amount');
            }
            if (!Schema::hasColumn('invoices', 'place_of_supply_code')) {
                $table->string('place_of_supply_code', 2)->nullable()->default('33')->after('place_of_supply');
            }
            if (!Schema::hasColumn('invoices', 'is_b2b')) {
                $table->boolean('is_b2b')->default(false)->after('place_of_supply_code');
            }
            if (!Schema::hasColumn('invoices', 'is_reverse_charge')) {
                $table->boolean('is_reverse_charge')->default(false)->after('is_b2b');
            }
            if (!Schema::hasColumn('invoices', 'cgst_amount')) {
                $table->decimal('cgst_amount', 10, 2)->default(0.00)->after('tax_amount');
            }
            if (!Schema::hasColumn('invoices', 'sgst_amount')) {
                $table->decimal('sgst_amount', 10, 2)->default(0.00)->after('cgst_amount');
            }
            if (!Schema::hasColumn('invoices', 'igst_amount')) {
                $table->decimal('igst_amount', 10, 2)->default(0.00)->after('sgst_amount');
            }
        });

        // 4. Purchase Orders: Supplier Invoice, Tax heads, ITC eligibility
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'supplier_invoice_no')) {
                $table->string('supplier_invoice_no')->nullable()->after('po_number');
            }
            if (!Schema::hasColumn('purchase_orders', 'supplier_invoice_date')) {
                $table->date('supplier_invoice_date')->nullable()->after('supplier_invoice_no');
            }
            if (!Schema::hasColumn('purchase_orders', 'cgst_amount')) {
                $table->decimal('cgst_amount', 12, 2)->default(0.00)->after('tax_amount');
            }
            if (!Schema::hasColumn('purchase_orders', 'sgst_amount')) {
                $table->decimal('sgst_amount', 12, 2)->default(0.00)->after('cgst_amount');
            }
            if (!Schema::hasColumn('purchase_orders', 'igst_amount')) {
                $table->decimal('igst_amount', 12, 2)->default(0.00)->after('sgst_amount');
            }
            if (!Schema::hasColumn('purchase_orders', 'is_itc_eligible')) {
                $table->boolean('is_itc_eligible')->default(true)->after('igst_amount');
            }
        });

        // 5. Gateway / Company Settings: Company GST profile
        Schema::table('gateway_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('gateway_settings', 'company_trade_name')) {
                $table->string('company_trade_name')->nullable()->default('CCTV Security & Surveillance Solutions')->after('id');
            }
            if (!Schema::hasColumn('gateway_settings', 'company_legal_name')) {
                $table->string('company_legal_name')->nullable()->after('company_trade_name');
            }
            if (!Schema::hasColumn('gateway_settings', 'company_gstin')) {
                $table->string('company_gstin', 15)->nullable()->default('33AAAAA0000A1Z5')->after('company_legal_name');
            }
            if (!Schema::hasColumn('gateway_settings', 'company_pan')) {
                $table->string('company_pan', 10)->nullable()->after('company_gstin');
            }
            if (!Schema::hasColumn('gateway_settings', 'company_state')) {
                $table->string('company_state', 100)->nullable()->default('Tamil Nadu')->after('company_pan');
            }
            if (!Schema::hasColumn('gateway_settings', 'company_state_code')) {
                $table->string('company_state_code', 2)->nullable()->default('33')->after('company_state');
            }
            if (!Schema::hasColumn('gateway_settings', 'company_address')) {
                $table->text('company_address')->nullable()->after('company_state_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['hsn_code', 'tax_rate']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['gstin', 'company_legal_name', 'state', 'state_code']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'place_of_supply', 'place_of_supply_code', 'is_b2b', 'is_reverse_charge',
                'cgst_amount', 'sgst_amount', 'igst_amount'
            ]);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_invoice_no', 'supplier_invoice_date', 'cgst_amount',
                'sgst_amount', 'igst_amount', 'is_itc_eligible'
            ]);
        });

        Schema::table('gateway_settings', function (Blueprint $table) {
            $table->dropColumn([
                'company_trade_name', 'company_legal_name', 'company_gstin',
                'company_pan', 'company_state', 'company_state_code', 'company_address'
            ]);
        });
    }
};
