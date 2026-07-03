<?php

declare(strict_types=1);

namespace App\Domains\Files\Policies;

use App\Domains\Files\Models\Document;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;

class DocumentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Document $document): bool
    {
        return $document->project->isMember($user);
    }

    public function download(User $user, Document $document): bool
    {
        return $document->project->isMember($user);
    }

    public function create(User $user, Project $project): bool
    {
        return false;
    }

    public function update(User $user, Document $document): bool
    {
        return false;
    }

    public function delete(User $user, Document $document): bool
    {
        return false;
    }
}
