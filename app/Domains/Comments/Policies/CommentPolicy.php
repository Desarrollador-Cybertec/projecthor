<?php

declare(strict_types=1);

namespace App\Domains\Comments\Policies;

use App\Domains\Comments\Models\Comment;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Model;

class CommentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Comment $comment): bool
    {
        return $comment->resolveProject()?->isMember($user) ?? false;
    }

    /**
     * A user may comment on anything that belongs to a project they are part of.
     */
    public function create(User $user, Model $commentable): bool
    {
        $project = $commentable instanceof Project
            ? $commentable
            : $commentable->project;

        return $project instanceof Project && $project->isMember($user);
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->author_id === $user->id;
    }

    public function changeStatus(User $user, Comment $comment): bool
    {
        return $comment->resolveProject()?->isMember($user) ?? false;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->author_id === $user->id;
    }
}
