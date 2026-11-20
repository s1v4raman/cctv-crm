<?php

namespace App\Policies;

use App\Models\GoodsReceipt;
use App\Models\User;

class GoodsReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOffice();
    }

    public function view(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->isOffice();
    }

    public function create(User $user): bool
    {
        return $user->isOffice();
    }

    public function pdf(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->isOffice();
    }

    public function delete(User $user, GoodsReceipt $goodsReceipt): bool
    {
        return $user->isAdmin();
    }
}
