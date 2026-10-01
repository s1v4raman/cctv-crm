<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('gateway_settings')->updateOrInsert(
            ['id' => 1],
            [
                'company_trade_name' => 'Precision IT Systems',
                'company_legal_name' => 'Precision IT Systems',
                'company_gstin'      => '33AHLPI3531N1Z8',
                'company_pan'        => 'AHLPI3531N',
                'company_state'      => 'Tamil Nadu',
                'company_state_code' => '33',
                'company_address'    => 'Plot No.553, Lig-1, 27th Street, Tamil Nadu Housing Board, Avadi, Chennai-600054.',
                'upi_merchant_name'  => 'Precision IT Systems',
                'smtp_from_name'     => 'Precision IT Systems',
                'smtp_from_address'  => 'precisionitsystem@gmail.com',
                'updated_at'         => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
