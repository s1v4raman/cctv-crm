<?php

namespace App\Policies;

use App\Models\User;

class WagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
