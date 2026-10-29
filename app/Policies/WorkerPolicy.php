<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Worker;

class WorkerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isInternal();
    }

    public function view(User $user, Worker $worker): bool
    {
        return $user->isInternal();
    }

    public function create(User $user): bool
    {
        return $user->isOffice() || $user->isTechnician();
    }

    public function update(User $user, Worker $worker): bool
    {
        return $user->isOffice();
    }

    public function delete(User $user, Worker $worker): bool
    {
        return $user->isAdmin();
    }
}
