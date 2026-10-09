<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sites table
        Schema::table('sites', function (Blueprint $table) {
            if (!Schema::hasColumn('sites', 'client_phone')) {
                $table->string('client_phone', 50)->nullable();
            }
            if (!Schema::hasColumn('sites', 'client_email')) {
                $table->string('client_email', 150)->nullable();
            }
            if (!Schema::hasColumn('sites', 'contact_phone')) {
                $table->string('contact_phone', 50)->nullable();
            }
            if (!Schema::hasColumn('sites', 'state')) {
                $table->string('state', 100)->nullable();
            }
            if (!Schema::hasColumn('sites', 'google_maps_url')) {
                $table->string('google_maps_url', 500)->nullable();
            }
        });

        // 2. Companies table
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'code')) {
                $table->string('code', 50)->nullable();
            }
            if (!Schema::hasColumn('companies', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        // 3. Project Worker Attendances table
        Schema::table('project_worker_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('project_worker_attendances', 'daily_rate_snapshot')) {
                $table->decimal('daily_rate_snapshot', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('project_worker_attendances', 'cabling_metres')) {
                $table->decimal('cabling_metres', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('project_worker_attendances', 'cabling_rate')) {
                $table->decimal('cabling_rate', 8, 2)->default(0.00);
            }
            if (!Schema::hasColumn('project_worker_attendances', 'cabling_amount')) {
                $table->decimal('cabling_amount', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('project_worker_attendances', 'extra_description')) {
                $table->string('extra_description')->nullable();
            }
            if (!Schema::hasColumn('project_worker_attendances', 'is_paid')) {
                $table->boolean('is_paid')->default(false);
            }
            if (!Schema::hasColumn('project_worker_attendances', 'paid_week_start')) {
                $table->date('paid_week_start')->nullable();
            }
            if (!Schema::hasColumn('project_worker_attendances', 'wage_payment_id')) {
                $table->unsignedBigInteger('wage_payment_id')->nullable();
            }
            if (!Schema::hasColumn('project_worker_attendances', 'notes')) {
                $table->text('notes')->nullable();
            }
        });

        // 4. IP Devices table
        Schema::table('ip_devices', function (Blueprint $table) {
            if (!Schema::hasColumn('ip_devices', 'device_name')) {
                $table->string('device_name')->default('Device');
            }
            if (!Schema::hasColumn('ip_devices', 'web_port')) {
                $table->unsignedInteger('web_port')->nullable()->default(80);
            }
            if (!Schema::hasColumn('ip_devices', 'rtsp_port')) {
                $table->unsignedInteger('rtsp_port')->nullable()->default(554);
            }
            if (!Schema::hasColumn('ip_devices', 'server_port')) {
                $table->unsignedInteger('server_port')->nullable()->default(8000);
            }
            if (!Schema::hasColumn('ip_devices', 'location')) {
                $table->string('location')->nullable();
            }
            if (!Schema::hasColumn('ip_devices', 'status')) {
                $table->string('status', 30)->nullable()->default('configured');
            }
        });

        // 5. Project Documents table
        Schema::table('project_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('project_documents', 'storage_disk')) {
                $table->string('storage_disk', 50)->nullable()->default('public');
            }
        });

        // 6. Wage Payments table
        Schema::table('wage_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('wage_payments', 'week_start')) {
                $table->date('week_start')->nullable();
            }
            if (!Schema::hasColumn('wage_payments', 'week_end')) {
                $table->date('week_end')->nullable();
            }
            if (!Schema::hasColumn('wage_payments', 'settled_by')) {
                $table->unsignedBigInteger('settled_by')->nullable();
            }
            if (!Schema::hasColumn('wage_payments', 'reference_notes')) {
                $table->string('reference_notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Safe no-op on rollback
    }
};
