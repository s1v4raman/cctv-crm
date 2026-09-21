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
        Schema::table('gateway_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('gateway_settings', 'smtp_mailer')) {
                $table->string('smtp_mailer')->default('smtp')->after('auto_receipt_email_enabled');
            }
            if (!Schema::hasColumn('gateway_settings', 'smtp_host')) {
                $table->string('smtp_host')->nullable()->after('smtp_mailer');
            }
            if (!Schema::hasColumn('gateway_settings', 'smtp_port')) {
                $table->integer('smtp_port')->default(587)->nullable()->after('smtp_host');
            }
            if (!Schema::hasColumn('gateway_settings', 'smtp_username')) {
                $table->string('smtp_username')->nullable()->after('smtp_port');
            }
            if (!Schema::hasColumn('gateway_settings', 'smtp_password')) {
                $table->string('smtp_password')->nullable()->after('smtp_username');
            }
            if (!Schema::hasColumn('gateway_settings', 'smtp_encryption')) {
                $table->string('smtp_encryption')->default('tls')->nullable()->after('smtp_password');
            }
            if (!Schema::hasColumn('gateway_settings', 'smtp_from_address')) {
                $table->string('smtp_from_address')->nullable()->after('smtp_encryption');
            }
            if (!Schema::hasColumn('gateway_settings', 'smtp_from_name')) {
                $table->string('smtp_from_name')->nullable()->after('smtp_from_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gateway_settings', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('gateway_settings', 'smtp_mailer') ? 'smtp_mailer' : null,
                Schema::hasColumn('gateway_settings', 'smtp_host') ? 'smtp_host' : null,
                Schema::hasColumn('gateway_settings', 'smtp_port') ? 'smtp_port' : null,
                Schema::hasColumn('gateway_settings', 'smtp_username') ? 'smtp_username' : null,
                Schema::hasColumn('gateway_settings', 'smtp_password') ? 'smtp_password' : null,
                Schema::hasColumn('gateway_settings', 'smtp_encryption') ? 'smtp_encryption' : null,
                Schema::hasColumn('gateway_settings', 'smtp_from_address') ? 'smtp_from_address' : null,
                Schema::hasColumn('gateway_settings', 'smtp_from_name') ? 'smtp_from_name' : null,
            ]));
        });
    }
};
