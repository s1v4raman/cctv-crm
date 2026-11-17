<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    public const CATEGORIES = [
        'CCTV Cameras & Recorders',
        'Hard Drives & Storage',
        'Networking (PoE Switches, Routers, Patch Panels)',
        'Cables & Conduits',
        'Power Supplies, Batteries & Racks',
    ];

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
        'payment_link',
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

        if ($templateType === 'payment_confirmation') {
            $msg = "💳 *PAYMENT CONFIRMATION & REMITTANCE ADVICE*\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "Dear *{$name}*,\n\n";
            $msg .= "We have remitted payment to your account. Details:\n\n";
            if (!empty($customMessage)) {
                $msg .= "{$customMessage}\n\n";
            }
            $msg .= "Kindly check your bank credit and confirm receipt / reconcile your ledger.\n\n";
            $msg .= "Best regards,\n*Precision IT Systems Accounts Team*\nPhone: +91 96772 57774";
            return $msg;
        }

        if ($templateType === 'po_dispatch') {
            $msg = "📋 *PURCHASE ORDER DISPATCH — PRECISION IT SYSTEMS*\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "Dear *{$name}*,\n\n";
            $msg .= "Please find our official Purchase Order details below:\n\n";
            if (!empty($customMessage)) {
                $msg .= "{$customMessage}\n\n";
            }
            $msg .= "Kindly confirm order acceptance and share delivery tracking details once dispatched.\n\n";
            $msg .= "Best regards,\n*Precision IT Systems Procurement Team*";
            return $msg;
        }

        if ($templateType === 'stock_inquiry' || $templateType === 'stock_check') {
            $msg = "📦 *STOCK & PRICE INQUIRY — PRECISION IT SYSTEMS*\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "Dear *{$name}*,\n\n";
            $msg .= "Greetings from Precision IT Systems.\n\n";
            $msg .= "Kindly share your current inventory availability, best dealer pricing, and dispatch lead times for the following items/SKUs:\n\n";
            if (!empty($customMessage)) {
                $msg .= "{$customMessage}\n\n";
            } else {
                $msg .= "1. [Camera / NVR / HDD / Cable / Network Switch & Accessories]\n\n";
            }
            $msg .= "🔹 *Delivery Destination:* Central Warehouse, Precision IT Systems\n";
            $msg .= "🔹 *Requirement Timeline:* Immediate / Within 2-3 Days\n\n";
            $msg .= "Please revert with ready stock quantity, GST inclusive/exclusive rates, and dispatch schedule.\n\n";
            $msg .= "Best regards,\n*Precision IT Systems Procurement Team*\nPhone: +91 96772 57774";
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
     * Determine if supplier has any registered banking or payment details.
     */
    public function hasBankDetails(): bool
    {
        return !empty($this->bank_name)
            || !empty($this->bank_account_number)
            || !empty($this->bank_ifsc)
            || !empty($this->upi_id)
            || !empty($this->payment_link);
    }

    /**
     * Get single formatted text block of all banking details for quick copy-pasting to netbanking portals.
     */
    public function formattedBankDetails(): string
    {
        $lines = [];
        $lines[] = "Beneficiary: " . ($this->company_name ?: $this->name);
        if ($this->bank_name) $lines[] = "Bank: " . $this->bank_name;
        if ($this->bank_account_number) $lines[] = "A/C No: " . $this->bank_account_number;
        if ($this->bank_ifsc) $lines[] = "IFSC: " . strtoupper($this->bank_ifsc);
        if ($this->bank_branch) $lines[] = "Branch: " . $this->bank_branch;
        if ($this->upi_id) $lines[] = "UPI ID: " . $this->upi_id;
        if ($this->payment_link) $lines[] = "Payment Link: " . $this->payment_link;

        return implode("\n", $lines);
    }

    /**
     * Map a product category string or keyword to a standardized supplier category.
     */
    public static function mapProductCategoryToSupplierCategory(?string $productCategory, ?string $itemName = null): ?string
    {
        $cat = strtolower(trim((string) $productCategory));
        $name = strtolower(trim((string) $itemName));

        // 1. CCTV Cameras & Recorders
        if (in_array($cat, ['camera', 'recorder', 'cctv', 'nvr', 'dvr', 'cctv cameras & recorders'])
            || preg_match('/\b(camera|nvr|dvr|dome|bullet|ptz|cctv|channel|4mp|2mp|8mp|hikvision|dahua|cp plus|uniview)\b/i', $name)) {
            return 'CCTV Cameras & Recorders';
        }

        // 2. Hard Drives & Storage
        if (in_array($cat, ['storage', 'hdd', 'hard_disk', 'hard drives & storage'])
            || preg_match('/\b(hdd|hard\s*disk|purple|skyhawk|surveillance\s*hdd|\dtb|\d\s*tb|western digital|seagate)\b/i', $name)) {
            return 'Hard Drives & Storage';
        }

        // 3. Networking (PoE Switches, Routers, Patch Panels)
        if (in_array($cat, ['networking', 'switch', 'router', 'networking (poe switches, routers, patch panels)'])
            || preg_match('/\b(poe|switch|router|patch\s*panel|rj45|gigabit|sfp|d-link|tp-link|cisco)\b/i', $name)) {
            return 'Networking (PoE Switches, Routers, Patch Panels)';
        }

        // 4. Cables & Conduits
        if (in_array($cat, ['cable', 'cables', 'conduit', 'wire', 'cables & conduits'])
            || preg_match('/\b(cable|cat6|cat5|lan\s*cable|wire|conduit|casing|pvc\s*pipe|patch\s*cord)\b/i', $name)) {
            return 'Cables & Conduits';
        }

        // 5. Power Supplies, Batteries & Racks
        if (in_array($cat, ['power', 'smps', 'ups', 'battery', 'rack', 'power supplies, batteries & racks'])
            || preg_match('/\b(smps|power\s*supply|ups|battery|rack|4u|6u|9u|12v|adapter)\b/i', $name)) {
            return 'Power Supplies, Batteries & Racks';
        }

        return null;
    }

    /**
     * Retrieve suggested preferred suppliers for a product category or item name.
     */
    public static function getSuggestedSuppliers(?string $productCategory, ?string $itemName = null): \Illuminate\Database\Eloquent\Collection
    {
        $supplierCat = static::mapProductCategoryToSupplierCategory($productCategory, $itemName);
        if (!$supplierCat) {
            return static::where('is_active', true)->take(4)->get();
        }

        return static::where('is_active', true)
            ->where(function ($q) use ($supplierCat) {
                $q->whereJsonContains('categories', $supplierCat)
                  ->orWhere('categories', 'like', "%{$supplierCat}%");
            })
            ->orderBy('name')
            ->get();
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
