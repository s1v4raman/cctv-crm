<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'gst_number',
        'address',
        'city',
        'state',
        'payment_terms',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'bank_branch',
        'upi_id',
        'categories',
        'brands_supplied',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'categories' => 'array',
        ];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class)->latest();
    }

    public function rmaClaims(): HasMany
    {
        return $this->hasMany(RmaClaim::class)->latest();
    }

    public function vendorPayments(): HasMany
    {
        return $this->hasMany(VendorPayment::class)->latest('payment_date');
    }

    public function totalPurchaseVolume(): float
    {
        if ($this->relationLoaded('purchaseOrders')) {
            return (float) $this->purchaseOrders
                ->whereIn('status', ['ordered', 'partially_received', 'received'])
                ->sum('total');
        }

        return (float) $this->purchaseOrders()
            ->whereIn('status', ['ordered', 'partially_received', 'received'])
            ->sum('total');
    }

    public function balancePayable(): float
    {
        $orders = $this->relationLoaded('purchaseOrders')
            ? $this->purchaseOrders->whereIn('status', ['ordered', 'partially_received', 'received'])
            : $this->purchaseOrders()->whereIn('status', ['ordered', 'partially_received', 'received'])->get();

        $total = (float) $orders->sum('total');
        $paid = (float) $orders->sum('amount_paid');

        return max(0.0, $total - $paid);
    }

    /**
     * Build formatted WhatsApp message for Supplier / Vendor communication.
     */
    public function buildWhatsAppMessage(?string $customMessage = null, string $templateType = 'inquiry'): string
    {
        $name = $this->contact_person ?: ($this->company_name ?: $this->name);

        if ($templateType === 'payment') {
            $balance = $this->balancePayable();
            $msg = "💳 *PAYMENT INQUIRY / STATEMENT — PRECISION IT SYSTEMS*\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "Dear *{$name}*,\n\n";
            $msg .= "Please find the current vendor ledger summary with Precision IT Systems:\n\n";
            $msg .= "🔹 *Supplier:* " . ($this->company_name ?: $this->name) . "\n";
            $msg .= "🔹 *Total Procurement Volume:* ₹" . number_format($this->totalPurchaseVolume(), 2) . "\n";
            $msg .= "🔹 *Current Outstanding Balance:* *₹" . number_format($balance, 2) . "*\n";
            if ($this->payment_terms) {
                $msg .= "🔹 *Payment Terms:* {$this->payment_terms}\n";
            }
            $msg .= "\n" . ($customMessage ?: "Kindly share the updated statement of account or outstanding invoice copies for reconciliation and payment scheduling.") . "\n\n";
            $msg .= "Best regards,\n*Precision IT Systems Finance Team*";
            return $msg;
        }

        if ($templateType === 'stock_check') {
            $msg = "📦 *STOCK AVAILABILITY & PRICING INQUIRY*\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "Dear *{$name}*,\n\n";
            $msg .= "Greetings from Precision IT Systems.\n\n";
            $msg .= ($customMessage ?: "We have upcoming project requirements. Please share your current inventory availability, delivery timelines, and updated dealer price list for CCTV, NVR, cabling, and networking gear.") . "\n\n";
            $msg .= "Best regards,\n*Precision IT Systems Procurement Team*";
            return $msg;
        }

        // General inquiry / Communique
        $msg = "👋 *PRECISION IT SYSTEMS — VENDOR COMMUNIQUE*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "Dear *{$name}*,\n\n";
        $msg .= ($customMessage ?: "Greetings from Precision IT Systems. We would like to follow up regarding our procurement collaboration and supply pipeline.") . "\n\n";
        $msg .= "Best regards,\n*Precision IT Systems*";
        return $msg;
    }

    /**
     * Get direct WhatsApp URL (wa.me) for this Supplier.
     */
    public function getWhatsAppUrl(?string $customPhone = null, ?string $customMessage = null, string $templateType = 'inquiry'): string
    {
        $phone = $customPhone ?: ($this->phone ?? '');
        $phoneClean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phoneClean) === 10) {
            $phoneClean = '91' . $phoneClean;
        }
        $text = $this->buildWhatsAppMessage($customMessage, $templateType);

        return "https://wa.me/{$phoneClean}?text=" . urlencode($text);
    }
}
