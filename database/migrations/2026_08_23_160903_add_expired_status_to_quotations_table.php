<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('quotations', function (Blueprint $table) {
            DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft','sent','accepted','rejected','expired') DEFAULT 'draft'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('quotations', function (Blueprint $table) {
            DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft','sent','accepted','rejected') DEFAULT 'draft'");
        });
    }
};

