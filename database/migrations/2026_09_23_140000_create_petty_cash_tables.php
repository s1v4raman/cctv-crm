<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Petty Cash Accounts (Main Vault / Office Safe, Technician Field Wallets, Bank Transit)
        if (!Schema::hasTable('petty_cash_accounts')) {
            Schema::create('petty_cash_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g. "Head Office Safe", "Field Wallet - [Technician]"
                $table->string('account_type')->default('field_wallet'); // main_vault, field_wallet, bank_transit
                $table->foreignId('custodian_id')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('current_balance', 12, 2)->default(0.00);
                $table->decimal('warning_limit', 12, 2)->default(10000.00); // High-float risk threshold
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['account_type', 'is_active']);
            });
        }

        // 2. Petty Cash Transactions (Advances, Field Collections, Site Expenses, Handovers/Deposits)
        if (!Schema::hasTable('petty_cash_transactions')) {
            Schema::create('petty_cash_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('voucher_no')->unique(); // e.g. PCV-YYYYMMDD-0001
                $table->foreignId('petty_cash_account_id')->constrained('petty_cash_accounts')->cascadeOnDelete();
                $table->foreignId('destination_account_id')->nullable()->constrained('petty_cash_accounts')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Actioned by
                $table->string('transaction_type'); // float_advance, field_collection, direct_expense, cash_deposit_handover, adjustment
                $table->decimal('amount', 12, 2);
                $table->date('transaction_date');
                $table->string('category')->nullable(); // materials, hardware_conduits, travel_fuel, food_refreshment, tolls_parking, customer_collection, advance_float, bank_deposit, office_supplies, other
                $table->foreignId('related_job_id')->nullable()->constrained('installation_jobs')->nullOnDelete();
                $table->foreignId('related_ticket_id')->nullable()->constrained('service_tickets')->nullOnDelete();
                $table->foreignId('related_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->foreignId('related_payment_id')->nullable()->constrained('payments')->nullOnDelete();
                $table->foreignId('related_expense_claim_id')->nullable()->constrained('expense_claims')->nullOnDelete();
                $table->string('vendor_payee_name')->nullable();
                $table->string('bill_receipt_no')->nullable();
                $table->string('receipt_photo_path')->nullable();
                $table->text('notes')->nullable();
                $table->string('status')->default('approved'); // pending, approved, rejected
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('approved_at')->nullable();
                $table->timestamps();

                $table->index(['petty_cash_account_id', 'transaction_date']);
                $table->index('transaction_type');
            });
        }

        // 3. Daily Cash Reconciliations & Denomination Breakdown
        if (!Schema::hasTable('daily_cash_reconciliations')) {
            Schema::create('daily_cash_reconciliations', function (Blueprint $table) {
                $table->id();
                $table->string('reconciliation_no')->unique(); // e.g. REC-CASH-YYYYMMDD-0001
                $table->foreignId('petty_cash_account_id')->constrained('petty_cash_accounts')->cascadeOnDelete();
                $table->foreignId('custodian_id')->nullable()->constrained('users')->nullOnDelete();
                $table->date('reconciliation_date');
                $table->decimal('opening_balance', 12, 2)->default(0.00);
                $table->decimal('total_inflow', 12, 2)->default(0.00);
                $table->decimal('total_outflow', 12, 2)->default(0.00);
                $table->decimal('system_expected_balance', 12, 2)->default(0.00);
                $table->decimal('physical_counted_balance', 12, 2)->default(0.00);
                $table->decimal('variance_amount', 12, 2)->default(0.00); // physical - expected
                $table->string('variance_status')->default('matched'); // matched, shortage, excess
                $table->json('denominations')->nullable(); // Counts of 2000, 500, 200, 100, 50, 20, 10, 5, 2, 1, coins
                $table->text('reconciliation_notes')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('verified_at')->nullable();
                $table->string('status')->default('submitted'); // draft, submitted, verified, disputed
                $table->timestamps();

                $table->index(['petty_cash_account_id', 'reconciliation_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_cash_reconciliations');
        Schema::dropIfExists('petty_cash_transactions');
        Schema::dropIfExists('petty_cash_accounts');
    }
};
