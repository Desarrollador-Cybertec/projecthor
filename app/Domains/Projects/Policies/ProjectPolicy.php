<?php

declare(strict_types=1);

namespace App\Domains\Projects\Policies;

use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;

class ProjectPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $project->isMember($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Project $project): bool
    {
        return $project->responsible_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return false;
    }

    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    public function finish(User $user, Project $project): bool
    {
        return $project->responsible_id === $user->id;
    }

    public function export(User $user, Project $project): bool
    {
        return $project->isMember($user);
    }
}
