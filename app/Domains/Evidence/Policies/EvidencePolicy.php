<?php

declare(strict_types=1);

namespace App\Domains\Evidence\Policies;

use App\Domains\Evidence\Models\Evidence;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;

class EvidencePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Evidence $evidence): bool
    {
        return $evidence->project->isMember($user);
    }

    public function create(User $user, Project $project): bool
    {
        return $project->isMember($user);
    }

    public function update(User $user, Evidence $evidence): bool
    {
        return $evidence->author_id === $user->id;
    }

    public function delete(User $user, Evidence $evidence): bool
    {
        return $evidence->author_id === $user->id;
    }
}
