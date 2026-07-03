<?php

declare(strict_types=1);

namespace App\Domains\Screenshots\Policies;

use App\Domains\Projects\Models\Project;
use App\Domains\Screenshots\Models\Screenshot;
use App\Domains\Users\Models\User;

class ScreenshotPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Screenshot $screenshot): bool
    {
        return $screenshot->project->isMember($user);
    }

    public function create(User $user, Project $project): bool
    {
        return $project->isMember($user);
    }

    public function update(User $user, Screenshot $screenshot): bool
    {
        return $screenshot->author_id === $user->id;
    }

    public function delete(User $user, Screenshot $screenshot): bool
    {
        return $screenshot->author_id === $user->id;
    }
}
