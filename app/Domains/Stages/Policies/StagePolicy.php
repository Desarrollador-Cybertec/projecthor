<?php

declare(strict_types=1);

namespace App\Domains\Stages\Policies;

use App\Domains\Stages\Models\Stage;
use App\Domains\Users\Models\User;

class StagePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Stage $stage): bool
    {
        return $stage->project->isMember($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Stage $stage): bool
    {
        return $stage->project->isMember($user);
    }

    public function delete(User $user, Stage $stage): bool
    {
        return false;
    }
}
