<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_documents') && !Schema::hasColumn('project_documents', 'document_type')) {
            Schema::table('project_documents', function (Blueprint $table) {
                $table->string('document_type', 50)->default('delivery_challan')->after('title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('project_documents') && Schema::hasColumn('project_documents', 'document_type')) {
            Schema::table('project_documents', function (Blueprint $table) {
                $table->dropColumn('document_type');
            });
        }
    }
};
