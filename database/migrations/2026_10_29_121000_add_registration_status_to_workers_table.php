<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 'pending' = signed up on the worker login page and waiting for an admin to approve.
     * Every existing worker was added by the office, so they start as 'approved'.
     */
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->string('registration_status', 20)->default('approved')->after('is_active')->index();
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropIndex(['registration_status']);
            $table->dropColumn('registration_status');
        });
    }
};
