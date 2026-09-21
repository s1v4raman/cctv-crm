<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->date('quotation_date')
                ->nullable()
                ->after('quotation_no');

            $table->date('valid_until')
                ->nullable()
                ->after('quotation_date');

            $table->decimal('discount', 10, 2)
                ->default(0)
                ->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'quotation_date',
                'valid_until',
                'discount',
            ]);
        });
    }
};