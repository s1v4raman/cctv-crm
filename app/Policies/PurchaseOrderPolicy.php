<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    /**
     * Determine whether the user can view any purchase orders.
     * Only office users (admin & staff) can view. Technicians and workers are denied (403).
     */
    public function viewAny(User $user): bool
    {
        return $user->isOffice();
    }

    /**
     * Determine whether the user can view the specific purchase order.
     */
    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isOffice();
    }

    /**
     * Determine whether the user can create purchase orders.
     */
    public function create(User $user): bool
    {
        return $user->isOffice();
    }

    /**
     * Determine whether the user can update the purchase order.
     * Only office users, and only when PO is in draft or rejected status.
     */
    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isOffice() && $purchaseOrder->canBeEdited();
    }

    /**
     * Determine whether the user can delete the purchase order.
     * Only admin, and only if draft, rejected, or cancelled.
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isAdmin() && in_array($purchaseOrder->status, [
            PurchaseOrder::STATUS_DRAFT,
            PurchaseOrder::STATUS_REJECTED,
            PurchaseOrder::STATUS_CANCELLED,
        ], true);
    }

    /**
     * Determine whether the user can update status through the state machine.
     */
    public function updateStatus(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isOffice();
    }

    /**
     * Determine whether the user can submit the PO for approval.
     */
    public function submitForApproval(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isOffice() && $purchaseOrder->status === PurchaseOrder::STATUS_DRAFT;
    }

    /**
     * Determine whether the user can approve the PO. Only admin.
     */
    public function approve(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isAdmin() && $purchaseOrder->status === PurchaseOrder::STATUS_PENDING_APPROVAL;
    }

    /**
     * Determine whether the user can reject the PO. Only admin.
     */
    public function reject(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isAdmin() && $purchaseOrder->status === PurchaseOrder::STATUS_PENDING_APPROVAL;
    }

    /**
     * Determine whether the user can inward stock items.
     */
    public function receive(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isOffice() && in_array($purchaseOrder->status, [
            PurchaseOrder::STATUS_SENT,
            PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            'ordered',
        ], true);
    }

    /**
     * Determine whether the user can update payment or financial settlement.
     */
    public function updatePayment(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isOffice();
    }
}
