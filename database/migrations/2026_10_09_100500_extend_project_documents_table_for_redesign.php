<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->string('document_type', 50)->default('delivery_challan')->after('title');
            $table->string('dc_number')->nullable()->index()->after('document_type');
            $table->date('dc_date')->nullable()->after('dc_number');
            $table->boolean('is_invoiced')->default(false)->index()->after('dc_date');
            $table->string('invoice_number')->nullable()->after('is_invoiced');
            $table->dateTime('invoiced_at')->nullable()->after('invoice_number');
            $table->json('items_summary')->nullable()->after('invoiced_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropColumn([
                'document_type',
                'dc_number',
                'dc_date',
                'is_invoiced',
                'invoice_number',
                'invoiced_at',
                'items_summary',
                'deleted_at',
            ]);
        });
    }
};
