<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    /**
     * Determine whether the user can view any suppliers.
     * Only office users (admin & staff). Technicians and workers are denied (403).
     */
    public function viewAny(User $user): bool
    {
        return $user->isOffice();
    }

    /**
     * Determine whether the user can view the specific supplier.
     */
    public function view(User $user, Supplier $supplier): bool
    {
        return $user->isOffice();
    }

    /**
     * Determine whether the user can use supplier suggestions / autocomplete.
     */
    public function suggest(User $user): bool
    {
        return $user->isOffice();
    }

    /**
     * Determine whether the user can create suppliers.
     */
    public function create(User $user): bool
    {
        return $user->isOffice();
    }

    /**
     * Determine whether the user can update the supplier.
     */
    public function update(User $user, Supplier $supplier): bool
    {
        return $user->isOffice();
    }

    /**
     * Determine whether the user can delete the supplier. Only admin.
     */
    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can record a payment against the supplier.
     */
    public function recordPayment(User $user, Supplier $supplier): bool
    {
        return $user->isOffice();
    }
}
