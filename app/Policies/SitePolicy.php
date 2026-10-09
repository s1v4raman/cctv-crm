<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;

class SitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isInternal();
    }

    public function view(User $user, Site $site): bool
    {
        return $user->isInternal();
    }

    public function create(User $user): bool
    {
        return $user->isInternal();
    }

    public function update(User $user, Site $site): bool
    {
        return $user->isAdmin() || $site->created_by === $user->id;
    }

    public function delete(User $user, Site $site): bool
    {
        return $user->isAdmin();
    }
}
