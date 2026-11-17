<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('payment_terms');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_ifsc')->nullable()->after('bank_account_number');
            $table->string('bank_branch')->nullable()->after('bank_ifsc');
            $table->string('upi_id')->nullable()->after('bank_branch');
            $table->json('categories')->nullable()->after('upi_id');
            $table->text('brands_supplied')->nullable()->after('categories');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account_number',
                'bank_ifsc',
                'bank_branch',
                'upi_id',
                'categories',
                'brands_supplied',
            ]);
        });
    }
};
