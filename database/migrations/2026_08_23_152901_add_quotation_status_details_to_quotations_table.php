<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: status_changed_at, status_changed_by, and rejection_reason
        // were already added by 2026_08_23_151829_add_status_dates_to_quotations_table.
    }

    public function down(): void
    {
        // No-op
    }
};