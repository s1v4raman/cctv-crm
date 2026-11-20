<?php

namespace App\Policies;

use App\Models\PurchaseReturn;
use App\Models\User;

class PurchaseReturnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOffice();
    }

    public function view(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->isOffice();
    }

    public function create(User $user): bool
    {
        return $user->isOffice();
    }

    public function confirm(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->isAdmin();
    }
}
