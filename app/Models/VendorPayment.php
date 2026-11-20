<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPayment extends Model
{
    use HasFactory;

    protected $table = 'vendor_payments';

    public const KIND_ADVANCE = 'advance';
    public const KIND_AGAINST_BILL = 'against_bill';

    protected $fillable = [
        'supplier_id',
        'purchase_order_id',
        'vendor_bill_id',
        'payment_reference',
        'payment_date',
        'amount',
        'kind',
        'payment_method',
        'transaction_reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function vendorBill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class, 'vendor_bill_id');
    }

    public function allocations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VendorPaymentAllocation::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generatePaymentReference(): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return "VPAY-{$year}-" . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Build formatted WhatsApp message for Payment Confirmation & Advice.
     */
    public function buildWhatsAppMessage(?string $customNote = null): string
    {
        $vendorName = $this->supplier?->contact_person ?: ($this->supplier?->company_name ?: $this->supplier?->name ?: 'Vendor Partner');
        $methodName = strtoupper(str_replace('_', ' / ', $this->payment_method ?: 'bank_transfer'));
        $date = $this->payment_date ? $this->payment_date->format('d M Y') : now()->format('d M Y');

        $msg = "💳 *PAYMENT CONFIRMATION & REMITTANCE ADVICE*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "Dear *{$vendorName}*,\n\n";
        $msg .= "We have remitted payment to your account. Please find transaction details below:\n\n";
        $msg .= "🔹 *Amount Paid:* *₹" . number_format((float) $this->amount, 2) . "*\n";
        $msg .= "🔹 *Payment Date:* {$date}\n";
        $msg .= "🔹 *Payment Method:* {$methodName}\n";
        if ($this->transaction_reference) {
            $msg .= "🔹 *UTR / Reference No:* *{$this->transaction_reference}*\n";
        }
        $msg .= "🔹 *Payment Reference:* {$this->payment_reference}\n";
        if ($this->purchaseOrder) {
            $msg .= "🔹 *Against Purchase Order:* {$this->purchaseOrder->po_number}\n";
        }
        if ($this->notes) {
            $msg .= "🔹 *Payment Remarks:* {$this->notes}\n";
        }
        if ($customNote) {
            $msg .= "📌 *Additional Note:* {$customNote}\n";
        }
        $msg .= "\nKindly check your bank credit and confirm receipt / reconcile your ledger.\n\n";
        $msg .= "Best regards,\n*Precision IT Systems Accounts Team*\nPhone: +91 96772 57774";

        return $msg;
    }

    /**
     * Get direct WhatsApp URL for this Payment.
     */
    public function getWhatsAppUrl(?string $customPhone = null, ?string $customNote = null): string
    {
        $phone = $customPhone ?: ($this->supplier?->phone ?? '');
        $phoneClean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phoneClean) === 10) {
            $phoneClean = '91' . $phoneClean;
        }
        $text = $this->buildWhatsAppMessage($customNote);

        return "https://api.whatsapp.com/send?phone={$phoneClean}&text=" . urlencode($text);
    }

    /**
     * Build standard email subject for Payment Confirmation.
     */
    public function buildEmailSubject(): string
    {
        $utr = $this->transaction_reference ? " (UTR: {$this->transaction_reference})" : "";
        $amount = number_format((float) $this->amount, 2);
        return "Payment Confirmation & Remittance Advice: ₹{$amount}{$utr} — Precision IT Systems";
    }

    /**
     * Build styled HTML email body for Payment Confirmation.
     */
    public function buildEmailHtml(?string $customNote = null): string
    {
        $vendorName = $this->supplier?->contact_person ?: ($this->supplier?->company_name ?: $this->supplier?->name ?: 'Vendor Partner');
        $methodName = strtoupper(str_replace('_', ' / ', $this->payment_method ?: 'bank_transfer'));
        $date = $this->payment_date ? $this->payment_date->format('d M Y') : now()->format('d M Y');
        $amountFormatted = number_format((float) $this->amount, 2);

        return "
            <div style='font-family: Arial, sans-serif; font-size: 14px; color: #1e293b; max-width: 650px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;'>
                <div style='background-color: #064e3b; padding: 22px 24px; color: #ffffff;'>
                    <table style='width: 100%;'>
                        <tr>
                            <td>
                                <h2 style='margin: 0; font-size: 18px; color: #34d399;'>Precision IT Systems</h2>
                                <p style='margin: 4px 0 0 0; font-size: 12px; color: #a7f3d0;'>Payment Remittance Advice & Finance Operations</p>
                            </td>
                            <td style='text-align: right;'>
                                <span style='display: inline-block; background-color: #047857; color: #ffffff; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 11px; font-family: monospace;'>{$this->payment_reference}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style='padding: 24px; background-color: #ffffff; line-height: 1.6;'>
                    <p style='margin-top: 0;'>Dear <strong>" . e($vendorName) . "</strong>,</p>
                    <p>We are pleased to inform you that payment has been successfully initiated to your registered account. Please find the remittance particulars below:</p>

                    <div style='margin: 20px 0; border: 1px solid #a7f3d0; border-radius: 8px; background-color: #ecfdf5; padding: 18px;'>
                        <div style='font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #065f46; font-weight: bold;'>Remitted Amount</div>
                        <div style='font-size: 26px; font-weight: bold; color: #047857; font-family: monospace; margin: 4px 0 14px 0;'>₹{$amountFormatted}</div>

                        <table style='width: 100%; font-size: 13px; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 6px 0; color: #64748b; width: 40%;'>Payment Date:</td>
                                <td style='padding: 6px 0; color: #0f172a; font-weight: bold;'>{$date}</td>
                            </tr>
                            <tr>
                                <td style='padding: 6px 0; color: #64748b;'>Payment Mode:</td>
                                <td style='padding: 6px 0; color: #0f172a; font-weight: bold;'>{$methodName}</td>
                            </tr>
                            " . ($this->transaction_reference ? "
                            <tr>
                                <td style='padding: 6px 0; color: #64748b;'>UTR / Reference No:</td>
                                <td style='padding: 6px 0; color: #047857; font-weight: bold; font-family: monospace; font-size: 14px;'>" . e($this->transaction_reference) . "</td>
                            </tr>
                            " : "") . "
                            " . ($this->purchaseOrder ? "
                            <tr>
                                <td style='padding: 6px 0; color: #64748b;'>Against Purchase Order:</td>
                                <td style='padding: 6px 0; color: #2563eb; font-weight: bold; font-family: monospace;'>" . e($this->purchaseOrder->po_number) . "</td>
                            </tr>
                            " : "") . "
                            " . ($this->notes ? "
                            <tr>
                                <td style='padding: 6px 0; color: #64748b;'>Remarks / Notes:</td>
                                <td style='padding: 6px 0; color: #334155;'>" . e($this->notes) . "</td>
                            </tr>
                            " : "") . "
                        </table>
                    </div>

                    " . ($customNote ? "<div style='margin: 16px 0; padding: 12px 16px; background-color: #f8fafc; border-left: 4px solid #10b981; font-size: 13px; color: #334155;'>" . nl2br(e($customNote)) . "</div>" : "") . "

                    <p style='font-size: 12px; color: #64748b;'>Kindly credit the remittance against the corresponding invoices and confirm receipt by acknowledging this email.</p>

                    <div style='margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b;'>
                        <strong>Precision IT Systems — Accounts & Finance Department</strong><br>
                        Phone: +91 96772 57774 | Email: operations@precisionit.co.in<br>
                        GSTIN: 33AAACP1234A1Z5
                    </div>
                </div>
            </div>
        ";
    }
}
