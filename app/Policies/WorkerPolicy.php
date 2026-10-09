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
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isTechnician();
    }

    public function update(User $user, Worker $worker): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Worker $worker): bool
    {
        return $user->isAdmin();
    }
}
