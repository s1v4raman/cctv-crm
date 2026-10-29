<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The method column was an enum of cash / bank_transfer / upi / cheque / other, so recording
     * a GPay or card payment failed on PostgreSQL. It becomes a plain string checked by
     * StorePaymentRequest against Payment::METHODS.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_method_check');
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->string('method', 30)->default('cash')->change();
        });
    }

    /**
     * Left as a string: the old enum would reject payments already recorded as GPay or card.
     */
    public function down(): void
    {
    }
};
