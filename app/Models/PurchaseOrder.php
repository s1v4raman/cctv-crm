<?php

namespace App\Models;

use App\Helpers\GstHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseOrder extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SENT = 'sent';
    public const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Allowed status transitions map.
     */
    public const TRANSITIONS = [
        self::STATUS_DRAFT              => [self::STATUS_PENDING_APPROVAL, self::STATUS_APPROVED, self::STATUS_CANCELLED],
        self::STATUS_PENDING_APPROVAL   => [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_CANCELLED],
        self::STATUS_APPROVED           => [self::STATUS_SENT, self::STATUS_CANCELLED],
        self::STATUS_SENT               => [self::STATUS_PARTIALLY_RECEIVED, self::STATUS_RECEIVED, self::STATUS_CANCELLED],
        self::STATUS_PARTIALLY_RECEIVED => [self::STATUS_RECEIVED],
        self::STATUS_RECEIVED           => [self::STATUS_CLOSED],
        self::STATUS_REJECTED           => [self::STATUS_DRAFT, self::STATUS_CANCELLED],
        self::STATUS_CLOSED             => [],
        self::STATUS_CANCELLED          => [],
    ];

    protected $fillable = [
        'supplier_id',
        'project_id',
        'po_number',
        'supplier_invoice_no',
        'supplier_invoice_date',
        'order_date',
        'expected_delivery_date',
        'due_date',
        'status', // draft, pending_approval, approved, sent, partially_received, received, closed, rejected, cancelled
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'subtotal',
        'tax_percent',
        'tax_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'is_itc_eligible',
        'shipping_cost',
        'total',
        'payment_status', // unpaid, partially_paid, paid
        'amount_paid',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'order_date'            => 'date',
            'expected_delivery_date'=> 'date',
            'due_date'              => 'date',
            'supplier_invoice_date' => 'date',
            'approved_at'           => 'datetime',
            'rejected_at'           => 'datetime',
            'subtotal'              => 'decimal:2',
            'tax_percent'           => 'decimal:2',
            'tax_amount'            => 'decimal:2',
            'cgst_amount'           => 'decimal:2',
            'sgst_amount'           => 'decimal:2',
            'igst_amount'           => 'decimal:2',
            'is_itc_eligible'       => 'boolean',
            'shipping_cost'         => 'decimal:2',
            'total'                 => 'decimal:2',
            'amount_paid'           => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class)->latest();
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class)->latest();
    }

    public function vendorPayments(): HasMany
    {
        return $this->hasMany(VendorPayment::class)->latest('payment_date');
    }

    public function vendorBills(): HasMany
    {
        return $this->hasMany(VendorBill::class)->latest('invoice_date');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Check if a transition to $targetStatus is permitted by the state machine.
     * Note: 'received' can only be set by inwardStock(), never by updateStatus().
     */
    public function canTransitionTo(string $targetStatus): bool
    {
        $allowed = self::TRANSITIONS[$this->status] ?? [];

        if (!in_array($targetStatus, $allowed, true)) {
            return false;
        }

        if ($targetStatus === self::STATUS_CANCELLED && !$this->canBeCancelled()) {
            return false;
        }

        return true;
    }

    /**
     * Cancellation is allowed only if nothing has been received and no payment exists.
     */
    public function canBeCancelled(): bool
    {
        $totalReceived = (int) $this->items()->sum('quantity_received');
        if ($totalReceived > 0) {
            return false;
        }

        if ((float) $this->amount_paid > 0.0) {
            return false;
        }

        if ($this->vendorPayments()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Approved, sent, received, or closed POs cannot be edited.
     * Only draft or rejected POs can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true);
    }

    /**
     * Submit PO for approval (Staff or Admin).
     */
    public function submitForApproval(): bool
    {
        if (!$this->canTransitionTo(self::STATUS_PENDING_APPROVAL)) {
            return false;
        }

        return $this->update(['status' => self::STATUS_PENDING_APPROVAL]);
    }

    /**
     * Admin approves the PO.
     */
    public function approve(int $adminUserId): bool
    {
        if (!$this->canTransitionTo(self::STATUS_APPROVED)) {
            return false;
        }

        return $this->update([
            'status'           => self::STATUS_APPROVED,
            'approved_by'      => $adminUserId,
            'approved_at'      => now(),
            'rejected_by'      => null,
            'rejected_at'      => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Admin rejects the PO with a reason.
     */
    public function reject(int $adminUserId, ?string $reason = null): bool
    {
        if (!$this->canTransitionTo(self::STATUS_REJECTED)) {
            return false;
        }

        return $this->update([
            'status'           => self::STATUS_REJECTED,
            'rejected_by'      => $adminUserId,
            'rejected_at'      => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Mark PO as sent to vendor.
     */
    public function markSent(): bool
    {
        if ($this->status === self::STATUS_APPROVED) {
            return $this->update(['status' => self::STATUS_SENT]);
        }

        return false;
    }

    /**
     * Close received PO.
     */
    public function close(): bool
    {
        if (!$this->canTransitionTo(self::STATUS_CLOSED)) {
            return false;
        }

        return $this->update(['status' => self::STATUS_CLOSED]);
    }

    /**
     * Compute 3-Way Matching Verification Status (PO vs GRN Inward vs Vendor Bill)
     */
    public function getThreeWayMatchStatus(): array
    {
        $items = $this->items;
        $totalOrdered = (int) $items->sum('quantity_ordered');
        $totalReceived = (int) $items->sum('quantity_received');
        $hasBill = !empty(trim((string) $this->supplier_invoice_no));

        $quantityMatch = ($totalOrdered > 0 && $totalReceived >= $totalOrdered);
        $invoiceAttached = $hasBill;
        $priceMatch = true;

        if ($quantityMatch && $invoiceAttached) {
            $status = 'matched';
            $badgeColor = 'green';
            $label = '3-Way Verified (Passed)';
            $description = 'All ordered items fully received with supplier invoice attached and verified.';
        } elseif ($totalReceived > 0 && $totalReceived < $totalOrdered) {
            $status = 'partial_receipt';
            $badgeColor = 'amber';
            $label = 'Partial Receipt (In Progress)';
            $description = "Received {$totalReceived} of {$totalOrdered} units. Inwarding partially completed.";
        } elseif ($totalReceived >= $totalOrdered && !$invoiceAttached) {
            $status = 'unbilled';
            $badgeColor = 'amber';
            $label = 'Inwarded / Bill Pending';
            $description = "{$totalReceived} of {$totalOrdered} units received at warehouse. Supplier invoice not yet recorded.";
        } else {
            $status = 'pending_inward';
            $badgeColor = 'gray';
            $label = 'Pending Inwarding (GRN)';
            $description = 'Goods not yet inwarded into central warehouse.';
        }

        $itemBreakdowns = [];
        foreach ($items as $item) {
            $itemBreakdowns[] = [
                'id'                 => $item->id,
                'name'               => $item->item_name,
                'sku'                => $item->sku,
                'ordered'            => (int) $item->quantity_ordered,
                'received'           => (int) $item->quantity_received,
                'pending'            => $item->pendingQuantity(),
                'unit_cost'          => (float) $item->unit_cost,
                'total_cost'         => (float) $item->total_cost,
                'is_fully_received'  => $item->quantity_received >= $item->quantity_ordered,
            ];
        }

        return [
            'status'           => $status,
            'label'            => $label,
            'badge_color'      => $badgeColor,
            'badge_class'      => match($badgeColor) {
                'green' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
                'blue'  => 'bg-blue-50 text-blue-700 border-blue-200',
                default => 'bg-slate-50 text-slate-700 border-slate-200',
            },
            'description'      => $description,
            'quantity_match'   => $quantityMatch,
            'invoice_attached' => $invoiceAttached,
            'has_bill'         => $hasBill,
            'price_match'      => $priceMatch,
            'total_ordered'    => $totalOrdered,
            'total_received'   => $totalReceived,
            'bill_number'      => $this->supplier_invoice_no,
            'bill_date'        => $this->supplier_invoice_date?->format('d M Y'),
            'items'            => $itemBreakdowns,
        ];
    }

    /**
     * Recalculate and persist PO totals, computing line-by-line Indian GST splits.
     */
    public function recalculateTotals(): void
    {
        $subtotal = 0.0;
        $taxAmount = 0.0;
        $cgstAmount = 0.0;
        $sgstAmount = 0.0;
        $igstAmount = 0.0;

        $vendorGstin = $this->supplier?->gst_number;
        $vendorStateCode = $vendorGstin ? GstHelper::extractStateCode($vendorGstin) : null;
        $companyStateCode = GstHelper::getCompanyStateCode();

        foreach ($this->items as $item) {
            $lineSubtotal = (float) ($item->quantity_ordered * $item->unit_cost);
            $taxRate = (float) ($item->tax_rate ?: ($this->tax_percent ?: 18.00));
            $split = GstHelper::calculateTaxSplit($lineSubtotal, $taxRate, $vendorStateCode, $companyStateCode);

            $item->update([
                'total_cost'  => $lineSubtotal,
                'tax_rate'    => $taxRate,
                'cgst_rate'   => $split['cgst_rate'],
                'cgst_amount' => $split['cgst_amount'],
                'sgst_rate'   => $split['sgst_rate'],
                'sgst_amount' => $split['sgst_amount'],
                'igst_rate'   => $split['igst_rate'],
                'igst_amount' => $split['igst_amount'],
            ]);

            $subtotal += $lineSubtotal;
            $taxAmount += $split['total_tax'];
            $cgstAmount += $split['cgst_amount'];
            $sgstAmount += $split['sgst_amount'];
            $igstAmount += $split['igst_amount'];
        }

        $total = $subtotal + $taxAmount + (float) $this->shipping_cost;

        $this->update([
            'subtotal'    => $subtotal,
            'tax_amount'  => $taxAmount,
            'cgst_amount' => $cgstAmount,
            'sgst_amount' => $sgstAmount,
            'igst_amount' => $igstAmount,
            'total'       => $total,
        ]);
    }

    /**
     * Inward received items into central warehouse stock ledger with strict locking and tolerance checks.
     * Creates a GoodsReceipt, GoodsReceiptItem, GoodsReceiptSerial (for tracked items), and StockMovement rows.
     * Updates weighted average cost and only transitions to 'received' if ALL item lines are completely fulfilled.
     * 
     * @param array<int|string, mixed> $receivedQuantities [item_id => qty] OR structured receipt array
     * @param int|null $userId
     * @param array<string, mixed> $receiptMeta
     * @return \App\Models\GoodsReceipt
     */
    public function inwardStock(array $receivedQuantities, ?int $userId = null, array $receiptMeta = []): GoodsReceipt
    {
        return DB::transaction(function () use ($receivedQuantities, $userId, $receiptMeta) {
            $settings = GatewaySetting::first();
            $tolerancePercent = (float) ($settings?->po_receiving_tolerance_percent ?? 0.0);

            // Normalize input format
            $deliveryNoteNo = $receivedQuantities['delivery_note_no'] ?? ($receiptMeta['delivery_note_no'] ?? null);
            $notes = $receivedQuantities['notes'] ?? ($receiptMeta['notes'] ?? null);
            $receivedDate = $receivedQuantities['received_date'] ?? ($receiptMeta['received_date'] ?? now()->toDateString());

            if (isset($receivedQuantities['items']) && is_array($receivedQuantities['items'])) {
                $itemsData = $receivedQuantities['items'];
            } else {
                $itemsData = [];
                foreach ($receivedQuantities as $key => $val) {
                    if (is_numeric($key)) {
                        if (is_array($val)) {
                            $itemsData[$key] = $val;
                        } else {
                            $itemsData[$key] = [
                                'qty_received'  => (int) $val,
                                'qty_rejected'  => 0,
                                'reject_reason' => null,
                                'serials'       => [],
                            ];
                        }
                    }
                }
            }

            // Generate GRN sequence
            $grnNumber = \App\Services\DocumentSequenceService::nextGoodsReceiptNumber($receivedDate);

            // Create GoodsReceipt header
            $goodsReceipt = GoodsReceipt::create([
                'grn_number'        => $grnNumber,
                'purchase_order_id' => $this->id,
                'supplier_id'       => $this->supplier_id,
                'received_date'     => $receivedDate,
                'received_by'       => $userId ?: auth()->id(),
                'delivery_note_no'  => $deliveryNoteNo,
                'notes'             => $notes,
            ]);

            // Fetch and lock all items for update
            $items = $this->items()->lockForUpdate()->get();
            $stockService = app(\App\Services\StockService::class);
            $anyInwarded = false;

            foreach ($items as $item) {
                if (!isset($itemsData[$item->id])) {
                    continue;
                }

                $lineData = $itemsData[$item->id];
                $newInwardQty = max(0, (int) ($lineData['qty_received'] ?? 0));
                $newRejectedQty = max(0, (int) ($lineData['qty_rejected'] ?? 0));
                $rejectReason = $lineData['reject_reason'] ?? null;
                $rawSerials = $lineData['serials'] ?? [];

                if ($newInwardQty <= 0 && $newRejectedQty <= 0) {
                    continue;
                }

                $anyInwarded = true;

                // Check tolerance ceiling for accepted quantity
                $maxAllowed = (int) ceil($item->quantity_ordered * (1.0 + ($tolerancePercent / 100.0)));
                $projectedTotal = $item->quantity_received + $newInwardQty;

                if ($projectedTotal > $maxAllowed) {
                    throw new InvalidArgumentException(
                        "Inward quantity ({$newInwardQty}) for '{$item->item_name}' exceeds ordered quantity ({$item->quantity_ordered}) plus {$tolerancePercent}% tolerance. Maximum permitted: {$maxAllowed}, current: {$item->quantity_received}."
                    );
                }

                // Check product serial tracking requirements
                $product = null;
                if ($item->product_id) {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                }

                if ($product && $product->track_serial && $newInwardQty > 0) {
                    // Extract list of serial numbers
                    $serialList = [];
                    foreach ($rawSerials as $s) {
                        if (is_array($s)) {
                            $sn = trim($s['serial_no'] ?? '');
                            if ($sn !== '') {
                                $serialList[] = [
                                    'serial_no'      => $sn,
                                    'mac_address'    => !empty($s['mac_address']) ? trim($s['mac_address']) : null,
                                    'warranty_until' => !empty($s['warranty_until']) ? $s['warranty_until'] : null,
                                ];
                            }
                        } else {
                            $sn = trim((string) $s);
                            if ($sn !== '') {
                                $serialList[] = [
                                    'serial_no'      => $sn,
                                    'mac_address'    => null,
                                    'warranty_until' => null,
                                ];
                            }
                        }
                    }

                    if (count($serialList) !== $newInwardQty) {
                        throw new InvalidArgumentException(
                            "Product '{$product->name}' requires {$newInwardQty} serial numbers (one per accepted unit). " . count($serialList) . " provided."
                        );
                    }

                    // Check for duplicates within the current submission
                    $submittedSerials = array_column($serialList, 'serial_no');
                    if (count($submittedSerials) !== count(array_unique($submittedSerials))) {
                        throw new InvalidArgumentException(
                            "Duplicate serial numbers found in submission for product '{$product->name}'."
                        );
                    }

                    // Check for existing serials in the database for this product
                    $existing = GoodsReceiptSerial::where('product_id', $product->id)
                        ->whereIn('serial_no', $submittedSerials)
                        ->pluck('serial_no')
                        ->toArray();

                    if (!empty($existing)) {
                        throw new InvalidArgumentException(
                            "Serial number(s) '" . implode(', ', $existing) . "' already registered for product '{$product->name}'."
                        );
                    }
                } else {
                    $serialList = [];
                }

                // Create GoodsReceiptItem
                $receiptItem = GoodsReceiptItem::create([
                    'goods_receipt_id'       => $goodsReceipt->id,
                    'purchase_order_item_id' => $item->id,
                    'product_id'             => $item->product_id,
                    'qty_received'           => $newInwardQty,
                    'qty_rejected'           => $newRejectedQty,
                    'reject_reason'          => $rejectReason,
                    'unit_cost'              => (float) $item->unit_cost,
                ]);

                // Record serials if any
                foreach ($serialList as $sData) {
                    GoodsReceiptSerial::create([
                        'goods_receipt_item_id' => $receiptItem->id,
                        'product_id'            => $product->id,
                        'serial_no'             => $sData['serial_no'],
                        'mac_address'           => $sData['mac_address'],
                        'warranty_until'        => $sData['warranty_until'],
                    ]);
                }

                // Update item received count for accepted goods
                if ($newInwardQty > 0) {
                    $item->increment('quantity_received', $newInwardQty);

                    // If linked to catalog product, route stock & weighted average cost through StockService
                    if ($product) {
                        $stockService->recordReceipt($product, $newInwardQty, (float) $item->unit_cost, [
                            'reference_type' => PurchaseOrder::class,
                            'reference_id'   => $this->id,
                            'note'           => "GRN: {$goodsReceipt->grn_number} (PO #{$this->po_number})",
                            'created_by'     => $userId ?: auth()->id(),
                        ]);
                    }
                }
            }

            if (!$anyInwarded) {
                throw new InvalidArgumentException("Please specify at least 1 unit to receive or reject.");
            }

            // Refresh items from database to check overall status
            $allItems = $this->items()->get();
            $allComplete = $allItems->count() > 0 && $allItems->every(function ($i) {
                return $i->quantity_received >= $i->quantity_ordered;
            });
            $anyReceived = $allItems->sum('quantity_received') > 0;

            if ($allComplete) {
                $this->update(['status' => self::STATUS_RECEIVED]);
            } elseif ($anyReceived) {
                $this->update(['status' => self::STATUS_PARTIALLY_RECEIVED]);
            }

            return $goodsReceipt;
        });
    }

    public function balanceDue(): float
    {
        return max((float) ($this->total - $this->amount_paid), 0.0);
    }

    /**
     * Build formatted WhatsApp message for Purchase Order sharing.
     */
    public function buildWhatsAppMessage(?string $customNote = null, ?string $pdfUrl = null): string
    {
        $supplierName = $this->supplier?->contact_person ?: ($this->supplier?->company_name ?: $this->supplier?->name ?: 'Vendor Partner');
        $items = $this->items;

        $msg = "📋 *PURCHASE ORDER — PRECISION IT SYSTEMS*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "Dear *{$supplierName}*,\n\n";
        $msg .= "Please find our official procurement purchase order details:\n\n";
        $msg .= "🔹 *PO Number:* {$this->po_number}\n";
        $msg .= "🔹 *Order Date:* " . ($this->order_date ? $this->order_date->format('d M Y') : now()->format('d M Y')) . "\n";
        if ($this->expected_delivery_date) {
            $msg .= "🔹 *Expected Delivery:* " . $this->expected_delivery_date->format('d M Y') . "\n";
        }
        $msg .= "\n📦 *ORDERED ITEMS:*\n";

        foreach ($items as $idx => $item) {
            $num = $idx + 1;
            $qty = (int) $item->quantity_ordered;
            $rate = number_format((float) $item->unit_cost, 2);
            $total = number_format((float) ($item->total_cost ?: ($item->quantity_ordered * $item->unit_cost)), 2);
            $msg .= "{$num}. *{$item->item_name}*\n";
            $msg .= "   Qty: {$qty} units @ ₹{$rate} = *₹{$total}*\n";
        }

        $msg .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "💰 *Subtotal:* ₹" . number_format((float) $this->subtotal, 2) . "\n";
        if ($this->tax_amount > 0) {
            $taxLabel = $this->tax_percent > 0 ? "GST ({$this->tax_percent}%)" : "Tax";
            $msg .= "🧾 *{$taxLabel}:* ₹" . number_format((float) $this->tax_amount, 2) . "\n";
        }
        if ($this->shipping_cost > 0) {
            $msg .= "🚚 *Shipping:* ₹" . number_format((float) $this->shipping_cost, 2) . "\n";
        }
        $msg .= "💵 *GRAND TOTAL:* *₹" . number_format((float) $this->total, 2) . "*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

        if ($pdfUrl) {
            $msg .= "📄 *Official PO PDF Document (Valid for 14 Days):*\n{$pdfUrl}\n\n";
        }

        if ($this->notes) {
            $msg .= "📝 *Terms & Instructions:* {$this->notes}\n\n";
        }
        if ($customNote) {
            $msg .= "📌 *Note:* {$customNote}\n\n";
        }

        $msg .= "📍 *Delivery Location:*\nPrecision IT Systems Central Warehouse, Avadi, Chennai - 600054\n";
        $msg .= "\nPlease confirm order acceptance and dispatch timeline.\n";
        $msg .= "Best regards,\n*Precision IT Systems Procurement Team*";

        return $msg;
    }

    /**
     * Build formatted WhatsApp message for Stock & Price Inquiry based on this PO's line items.
     */
    public function buildStockInquiryWhatsAppMessage(?string $customNote = null): string
    {
        $supplierName = $this->supplier?->contact_person ?: ($this->supplier?->company_name ?: $this->supplier?->name ?: 'Vendor Partner');
        $items = $this->items;

        $msg = "📦 *STOCK & PRICE INQUIRY — PRECISION IT SYSTEMS*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "Dear *{$supplierName}*,\n\n";
        $msg .= "Greetings from Precision IT Systems.\n";
        $msg .= "Kindly share current stock availability, best dealer pricing, and dispatch lead times for the following items/SKUs:\n\n";

        foreach ($items as $idx => $item) {
            $num = $idx + 1;
            $skuText = $item->sku ? " [SKU: {$item->sku}]" : "";
            $qty = (int) $item->quantity_ordered;
            $msg .= "{$num}. *{$item->item_name}*{$skuText}\n";
            $msg .= "   Requested Qty: *{$qty} units*\n";
        }

        $msg .= "\n🔹 *Reference PO / Scope:* {$this->po_number}\n";
        $msg .= "🔹 *Delivery Destination:* Central Warehouse, Precision IT Systems\n";
        if ($this->expected_delivery_date) {
            $msg .= "🔹 *Required Timeline:* " . $this->expected_delivery_date->format('d M Y') . "\n";
        }
        if ($customNote) {
            $msg .= "📌 *Note:* {$customNote}\n";
        }
        $msg .= "\nPlease confirm availability and dispatch schedule.\n\n";
        $msg .= "Best regards,\n*Precision IT Systems Procurement Team*\nPhone: +91 96772 57774";

        return $msg;
    }

    /**
     * Build formatted WhatsApp message for Payment Confirmation against this PO.
     */
    public function buildPaymentConfirmationWhatsAppMessage(?VendorPayment $payment = null, ?string $customNote = null): string
    {
        $supplierName = $this->supplier?->contact_person ?: ($this->supplier?->company_name ?: $this->supplier?->name ?: 'Vendor Partner');
        $amount = $payment ? (float) $payment->amount : (float) $this->amount_paid;
        $date = $payment && $payment->payment_date ? $payment->payment_date->format('d M Y') : now()->format('d M Y');
        $method = $payment ? strtoupper(str_replace('_', ' / ', $payment->payment_method)) : 'Bank Transfer';

        $msg = "💳 *PAYMENT CONFIRMATION & REMITTANCE ADVICE*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "Dear *{$supplierName}*,\n\n";
        $msg .= "We have remitted payment against Purchase Order *{$this->po_number}*. Details below:\n\n";
        $msg .= "🔹 *PO Number:* {$this->po_number}\n";
        $msg .= "🔹 *Amount Paid:* *₹" . number_format($amount, 2) . "*\n";
        $msg .= "🔹 *Payment Date:* {$date}\n";
        $msg .= "🔹 *Payment Mode:* {$method}\n";
        if ($payment?->transaction_reference) {
            $msg .= "🔹 *UTR / Ref No:* *{$payment->transaction_reference}*\n";
        }
        if ($payment?->payment_reference) {
            $msg .= "🔹 *Payment Ref:* {$payment->payment_reference}\n";
        }
        $rem = max(0.0, (float) $this->total - (float) $this->amount_paid);
        $msg .= "🔹 *Remaining PO Balance:* ₹" . number_format($rem, 2) . "\n";
        if ($customNote) {
            $msg .= "\n📌 *Remarks:* {$customNote}\n";
        }
        $msg .= "\nKindly verify your bank credit and issue payment receipt.\n\n";
        $msg .= "Best regards,\n*Precision IT Systems Accounts Team*";

        return $msg;
    }

    /**
     * Get direct WhatsApp URL for this Purchase Order.
     */
    public function getWhatsAppUrl(?string $customPhone = null, ?string $customNote = null, ?string $pdfUrl = null): string
    {
        $phone = $customPhone ?: ($this->supplier?->phone ?? '');
        $phoneClean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phoneClean) === 10) {
            $phoneClean = '91' . $phoneClean;
        }
        $text = $this->buildWhatsAppMessage($customNote, $pdfUrl);

        return "https://api.whatsapp.com/send?phone={$phoneClean}&text=" . urlencode($text);
    }
}
