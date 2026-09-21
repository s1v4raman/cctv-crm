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
        // 1. Add Online Payment & UPI Gateway settings
        Schema::table('gateway_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('gateway_settings', 'enable_online_payments')) {
                $table->boolean('enable_online_payments')->default(false)->after('otp_expiry_minutes');
            }
            if (!Schema::hasColumn('gateway_settings', 'razorpay_key_id')) {
                $table->string('razorpay_key_id')->nullable()->after('enable_online_payments');
            }
            if (!Schema::hasColumn('gateway_settings', 'razorpay_key_secret')) {
                $table->string('razorpay_key_secret')->nullable()->after('razorpay_key_id');
            }
            if (!Schema::hasColumn('gateway_settings', 'razorpay_webhook_secret')) {
                $table->string('razorpay_webhook_secret')->nullable()->after('razorpay_key_secret');
            }
            if (!Schema::hasColumn('gateway_settings', 'upi_vpa_id')) {
                $table->string('upi_vpa_id')->nullable()->after('razorpay_webhook_secret');
            }
            if (!Schema::hasColumn('gateway_settings', 'upi_merchant_name')) {
                $table->string('upi_merchant_name')->nullable()->after('upi_vpa_id');
            }
            if (!Schema::hasColumn('gateway_settings', 'auto_receipt_whatsapp_enabled')) {
                $table->boolean('auto_receipt_whatsapp_enabled')->default(true)->after('upi_merchant_name');
            }
            if (!Schema::hasColumn('gateway_settings', 'auto_receipt_email_enabled')) {
                $table->boolean('auto_receipt_email_enabled')->default(true)->after('auto_receipt_whatsapp_enabled');
            }
        });

        // 2. Add Online Payment tracking & Receipts fields to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->nullable()->change();

            if (!Schema::hasColumn('payments', 'receipt_no')) {
                $table->string('receipt_no')->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('payments', 'quotation_id')) {
                $table->foreignId('quotation_id')->nullable()->after('invoice_id')->constrained('quotations')->nullOnDelete();
            }
            if (!Schema::hasColumn('payments', 'gateway_order_id')) {
                $table->string('gateway_order_id')->nullable()->after('reference_no');
            }
            if (!Schema::hasColumn('payments', 'gateway_payment_id')) {
                $table->string('gateway_payment_id')->nullable()->after('gateway_order_id');
            }
            if (!Schema::hasColumn('payments', 'gateway_signature')) {
                $table->string('gateway_signature')->nullable()->after('gateway_payment_id');
            }
            if (!Schema::hasColumn('payments', 'payment_status')) {
                $table->string('payment_status')->default('completed')->after('gateway_signature');
            }
            if (!Schema::hasColumn('payments', 'receipt_pdf_path')) {
                $table->string('receipt_pdf_path')->nullable()->after('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'quotation_id')) {
                $table->dropForeign(['quotation_id']);
                $table->dropColumn('quotation_id');
            }
            $table->dropColumn(array_filter([
                Schema::hasColumn('payments', 'receipt_no') ? 'receipt_no' : null,
                Schema::hasColumn('payments', 'gateway_order_id') ? 'gateway_order_id' : null,
                Schema::hasColumn('payments', 'gateway_payment_id') ? 'gateway_payment_id' : null,
                Schema::hasColumn('payments', 'gateway_signature') ? 'gateway_signature' : null,
                Schema::hasColumn('payments', 'payment_status') ? 'payment_status' : null,
                Schema::hasColumn('payments', 'receipt_pdf_path') ? 'receipt_pdf_path' : null,
            ]));
        });

        Schema::table('gateway_settings', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('gateway_settings', 'enable_online_payments') ? 'enable_online_payments' : null,
                Schema::hasColumn('gateway_settings', 'razorpay_key_id') ? 'razorpay_key_id' : null,
                Schema::hasColumn('gateway_settings', 'razorpay_key_secret') ? 'razorpay_key_secret' : null,
                Schema::hasColumn('gateway_settings', 'razorpay_webhook_secret') ? 'razorpay_webhook_secret' : null,
                Schema::hasColumn('gateway_settings', 'upi_vpa_id') ? 'upi_vpa_id' : null,
                Schema::hasColumn('gateway_settings', 'upi_merchant_name') ? 'upi_merchant_name' : null,
                Schema::hasColumn('gateway_settings', 'auto_receipt_whatsapp_enabled') ? 'auto_receipt_whatsapp_enabled' : null,
                Schema::hasColumn('gateway_settings', 'auto_receipt_email_enabled') ? 'auto_receipt_email_enabled' : null,
            ]));
        });
    }
};
