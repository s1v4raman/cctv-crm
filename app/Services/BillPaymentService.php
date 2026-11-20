<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\VendorBill;
use App\Models\VendorPayment;
use App\Models\VendorPaymentAllocation;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BillPaymentService
{
    /**
     * Record a vendor payment against one or multiple bills, or as an advance against a PO.
     * Enforces admin permissions, row locking, and over-payment guards inside a transaction.
     *
     * @param array<string, mixed> $data
     * @param int|null $userId
     * @return VendorPayment
     */
    public function recordPayment(array $data, ?int $userId = null): VendorPayment
    {
        $adminId = $userId ?: auth()->id();
        $user = User::find($adminId);
        if (!$user || !$user->isAdmin()) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Only administrators can record vendor payments.');
        }

        return DB::transaction(function () use ($data, $adminId) {
            $supplierId = $data['supplier_id'] ?? null;
            $kind = $data['kind'] ?? VendorPayment::KIND_AGAINST_BILL;
            $paymentDate = $data['payment_date'] ?? now()->toDateString();
            $paymentMethod = $data['payment_method'] ?? 'bank_transfer';
            $transactionRef = $data['transaction_reference'] ?? null;
            $notes = $data['notes'] ?? null;
            $totalAmount = (float) ($data['amount'] ?? 0.0);

            if ($totalAmount <= 0.001) {
                throw new InvalidArgumentException('Payment amount must be greater than zero.');
            }

            // Scenario 1: Advance payment against a Purchase Order
            if ($kind === VendorPayment::KIND_ADVANCE || (!empty($data['purchase_order_id']) && empty($data['vendor_bill_id']) && empty($data['allocations']))) {
                if (empty($data['purchase_order_id'])) {
                    throw new InvalidArgumentException('Purchase Order reference is required for advance payments.');
                }

                $po = PurchaseOrder::where('id', $data['purchase_order_id'])->lockForUpdate()->firstOrFail();
                $poBalance = (float) $po->balanceDue();

                if ($totalAmount > ($poBalance + 0.001)) {
                    throw new InvalidArgumentException(
                        "Advance payment amount ₹" . number_format($totalAmount, 2) . " exceeds PO open balance ₹" . number_format($poBalance, 2) . " (Over-payment blocked)."
                    );
                }

                $po->increment('amount_paid', $totalAmount);
                $po->refresh();
                $po->update([
                    'payment_status' => $po->amount_paid >= ($po->total - 0.001) ? 'paid' : 'partially_paid',
                ]);

                return VendorPayment::create([
                    'supplier_id'           => $po->supplier_id,
                    'purchase_order_id'     => $po->id,
                    'vendor_bill_id'        => null,
                    'payment_reference'     => VendorPayment::generatePaymentReference(),
                    'payment_date'          => $paymentDate,
                    'amount'                => $totalAmount,
                    'kind'                  => VendorPayment::KIND_ADVANCE,
                    'payment_method'        => $paymentMethod,
                    'transaction_reference' => $transactionRef,
                    'notes'                 => $notes,
                    'created_by'            => $adminId,
                ]);
            }

            // Scenario 2: Split payment across multiple bills
            if (!empty($data['allocations']) && is_array($data['allocations'])) {
                $allocations = $data['allocations']; // [bill_id => amount]
                $sumAllocated = array_sum(array_map('floatval', $allocations));

                if (abs($sumAllocated - $totalAmount) > 0.01) {
                    throw new InvalidArgumentException(
                        "Sum of allocated bill amounts (₹" . number_format($sumAllocated, 2) . ") does not equal total payment amount (₹" . number_format($totalAmount, 2) . ")."
                    );
                }

                $payment = VendorPayment::create([
                    'supplier_id'           => $supplierId,
                    'purchase_order_id'     => null,
                    'vendor_bill_id'        => null,
                    'payment_reference'     => VendorPayment::generatePaymentReference(),
                    'payment_date'          => $paymentDate,
                    'amount'                => $totalAmount,
                    'kind'                  => VendorPayment::KIND_AGAINST_BILL,
                    'payment_method'        => $paymentMethod,
                    'transaction_reference' => $transactionRef,
                    'notes'                 => $notes,
                    'created_by'            => $adminId,
                ]);

                foreach ($allocations as $billId => $allocAmount) {
                    $allocAmount = (float) $allocAmount;
                    if ($allocAmount <= 0.001) {
                        continue;
                    }

                    $bill = VendorBill::where('id', $billId)->lockForUpdate()->firstOrFail();
                    $unpaid = $bill->balanceDue();

                    if ($allocAmount > ($unpaid + 0.001)) {
                        throw new InvalidArgumentException(
                            "Allocated payment ₹" . number_format($allocAmount, 2) . " exceeds unpaid balance ₹" . number_format($unpaid, 2) . " on Bill #{$bill->bill_number} (Over-payment blocked)."
                        );
                    }

                    $bill->amount_paid += $allocAmount;
                    $bill->status = $bill->amount_paid >= ($bill->total - 0.001) ? VendorBill::STATUS_PAID : VendorBill::STATUS_PARTIALLY_PAID;
                    $bill->save();

                    VendorPaymentAllocation::create([
                        'vendor_payment_id' => $payment->id,
                        'vendor_bill_id'    => $bill->id,
                        'amount'            => $allocAmount,
                    ]);

                    // Update PO if linked
                    if ($bill->purchase_order_id) {
                        $po = PurchaseOrder::where('id', $bill->purchase_order_id)->lockForUpdate()->first();
                        if ($po) {
                            $po->increment('amount_paid', $allocAmount);
                            $po->refresh();
                            $po->update([
                                'payment_status' => $po->amount_paid >= ($po->total - 0.001) ? 'paid' : 'partially_paid',
                            ]);
                        }
                    }
                }

                return $payment;
            }

            // Scenario 3: Single bill payment
            if (!empty($data['vendor_bill_id'])) {
                $bill = VendorBill::where('id', $data['vendor_bill_id'])->lockForUpdate()->firstOrFail();
                $unpaid = $bill->balanceDue();

                if ($totalAmount > ($unpaid + 0.001)) {
                    throw new InvalidArgumentException(
                        "Payment amount ₹" . number_format($totalAmount, 2) . " exceeds unpaid balance ₹" . number_format($unpaid, 2) . " on Bill #{$bill->bill_number} (Over-payment blocked)."
                    );
                }

                $bill->amount_paid += $totalAmount;
                $bill->status = $bill->amount_paid >= ($bill->total - 0.001) ? VendorBill::STATUS_PAID : VendorBill::STATUS_PARTIALLY_PAID;
                $bill->save();

                $payment = VendorPayment::create([
                    'supplier_id'           => $bill->supplier_id,
                    'purchase_order_id'     => $bill->purchase_order_id,
                    'vendor_bill_id'        => $bill->id,
                    'payment_reference'     => VendorPayment::generatePaymentReference(),
                    'payment_date'          => $paymentDate,
                    'amount'                => $totalAmount,
                    'kind'                  => VendorPayment::KIND_AGAINST_BILL,
                    'payment_method'        => $paymentMethod,
                    'transaction_reference' => $transactionRef,
                    'notes'                 => $notes,
                    'created_by'            => $adminId,
                ]);

                VendorPaymentAllocation::create([
                    'vendor_payment_id' => $payment->id,
                    'vendor_bill_id'    => $bill->id,
                    'amount'            => $totalAmount,
                ]);

                // Update PO if linked
                if ($bill->purchase_order_id) {
                    $po = PurchaseOrder::where('id', $bill->purchase_order_id)->lockForUpdate()->first();
                    if ($po) {
                        $po->increment('amount_paid', $totalAmount);
                        $po->refresh();
                        $po->update([
                            'payment_status' => $po->amount_paid >= ($po->total - 0.001) ? 'paid' : 'partially_paid',
                        ]);
                    }
                }

                return $payment;
            }

            throw new InvalidArgumentException('Please specify a Vendor Bill, PO, or allocation list for payment.');
        });
    }

    /**
     * Adjust an advance payment previously made on a PO towards a later vendor bill.
     */
    public function adjustAdvanceToBill(VendorPayment $advancePayment, VendorBill $bill, float $amount, ?int $userId = null): VendorPaymentAllocation
    {
        $adminId = $userId ?: auth()->id();
        $user = User::find($adminId);
        if (!$user || !$user->isAdmin()) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Only administrators can adjust advance payments.');
        }

        return DB::transaction(function () use ($advancePayment, $bill, $amount) {
            $payment = VendorPayment::where('id', $advancePayment->id)->lockForUpdate()->firstOrFail();
            $lockedBill = VendorBill::where('id', $bill->id)->lockForUpdate()->firstOrFail();

            if ($payment->kind !== VendorPayment::KIND_ADVANCE) {
                throw new InvalidArgumentException('Only advance payments can be adjusted against bills.');
            }

            $alreadyAllocated = (float) $payment->allocations()->sum('amount');
            $availableAdvance = (float) $payment->amount - $alreadyAllocated;

            if ($amount > ($availableAdvance + 0.001)) {
                throw new InvalidArgumentException(
                    "Adjustment amount ₹" . number_format($amount, 2) . " exceeds available advance balance ₹" . number_format($availableAdvance, 2) . "."
                );
            }

            $billUnpaid = $lockedBill->balanceDue();
            if ($amount > ($billUnpaid + 0.001)) {
                throw new InvalidArgumentException(
                    "Adjustment amount ₹" . number_format($amount, 2) . " exceeds unpaid balance ₹" . number_format($billUnpaid, 2) . " on Bill #{$lockedBill->bill_number}."
                );
            }

            $lockedBill->amount_paid += $amount;
            $lockedBill->status = $lockedBill->amount_paid >= ($lockedBill->total - 0.001) ? VendorBill::STATUS_PAID : VendorBill::STATUS_PARTIALLY_PAID;
            $lockedBill->save();

            // Link bill if not set yet
            if (!$payment->vendor_bill_id) {
                $payment->update(['vendor_bill_id' => $lockedBill->id]);
            }

            return VendorPaymentAllocation::create([
                'vendor_payment_id' => $payment->id,
                'vendor_bill_id'    => $lockedBill->id,
                'amount'            => $amount,
            ]);
        });
    }
}
