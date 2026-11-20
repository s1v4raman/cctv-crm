<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VendorBill;

class VendorBillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOffice();
    }

    public function view(User $user, VendorBill $bill): bool
    {
        return $user->isOffice();
    }

    public function create(User $user): bool
    {
        return $user->isOffice();
    }

    public function update(User $user, VendorBill $bill): bool
    {
        return $user->isOffice() && in_array($bill->status, [VendorBill::STATUS_DRAFT, VendorBill::STATUS_ON_HOLD], true);
    }

    public function approve(User $user, VendorBill $bill): bool
    {
        return $user->isAdmin();
    }

    public function pay(User $user, VendorBill $bill): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, VendorBill $bill): bool
    {
        return $user->isAdmin() && in_array($bill->status, [VendorBill::STATUS_DRAFT, VendorBill::STATUS_CANCELLED], true);
    }

    public function downloadInvoiceFile(User $user, VendorBill $bill): bool
    {
        return $user->isOffice();
    }
}
