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
        Schema::table('installation_jobs', function (Blueprint $table) {
            $table->foreignId('assigned_technician_id')
                ->nullable()
                ->after('scheduled_date')
                ->constrained('users')
                ->nullOnDelete();

            $table->dropColumn('assigned_technician');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installation_jobs', function (Blueprint $table) {
            $table->string('assigned_technician')
                ->nullable()
                ->after('scheduled_date');

            $table->dropConstrainedForeignId('assigned_technician_id');
        });
    }
};
