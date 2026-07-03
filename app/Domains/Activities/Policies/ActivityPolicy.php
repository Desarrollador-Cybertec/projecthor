<?php

declare(strict_types=1);

namespace App\Domains\Activities\Policies;

use App\Domains\Activities\Models\Activity;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;

class ActivityPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Activity $activity): bool
    {
        return $activity->project->isMember($user);
    }

    public function create(User $user, Project $project): bool
    {
        return $project->isMember($user);
    }

    public function update(User $user, Activity $activity): bool
    {
        return $activity->project->isMember($user);
    }

    public function changeStatus(User $user, Activity $activity): bool
    {
        return $activity->project->isMember($user);
    }

    public function delete(User $user, Activity $activity): bool
    {
        return false;
    }
}
