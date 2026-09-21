<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Quotation $quotation): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $quotation->status === 'draft';
    }

    /**
     * Only draft quotations can be deleted.
     */
    public function delete(User $user, Quotation $quotation): bool
    {
        return $quotation->status === 'draft';
    }

    public function restore(User $user, Quotation $quotation): bool
    {
        return false;
    }

    public function forceDelete(User $user, Quotation $quotation): bool
    {
        return false;
    }
}
