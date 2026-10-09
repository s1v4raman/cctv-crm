<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isInternal();
    }

    public function view(User $user, Project $project): bool
    {
        return $user->isInternal();
    }

    public function create(User $user): bool
    {
        return $user->isInternal();
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Creator can edit before approval
        return in_array($project->status, ['draft', 'pending_approval']) && $project->created_by === $user->id;
    }

    public function approve(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function updateStatus(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function uploadDocuments(User $user, Project $project): bool
    {
        return $user->isInternal();
    }

    public function deleteDocuments(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function logWorkDay(User $user, Project $project): bool
    {
        return $user->isAdmin() || $user->isTechnician();
    }

    public function manageDevices(User $user, Project $project): bool
    {
        return $user->isAdmin() || $user->isTechnician();
    }
}
