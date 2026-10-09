<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->after('lead_id')->constrained('sites')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->after('company_name')->constrained('companies')->nullOnDelete();
            $table->foreignId('lead_technician_id')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->dateTime('approved_on')->nullable()->after('approved_by');
            $table->decimal('per_metre_rate', 8, 2)->default(0.00)->after('budget');
            $table->string('po_number')->nullable()->after('project_code');
            $table->decimal('approved_value', 12, 2)->default(0.00)->after('budget');
            $table->json('requirements')->nullable()->after('description');
            $table->text('rejection_reason')->nullable()->after('notes');
            $table->string('status', 50)->default('draft')->change();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['lead_technician_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'site_id',
                'company_id',
                'lead_technician_id',
                'approved_by',
                'approved_on',
                'per_metre_rate',
                'po_number',
                'approved_value',
                'requirements',
                'rejection_reason',
                'deleted_at',
            ]);
        });
    }
};
