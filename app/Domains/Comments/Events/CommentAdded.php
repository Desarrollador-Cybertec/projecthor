<?php

declare(strict_types=1);

namespace App\Domains\Comments\Events;

use App\Domains\Comments\Models\Comment;
use App\Domains\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class CommentAdded
{
    use Dispatchable;

    public function __construct(
        public Comment $comment,
        public ?User $actor = null,
    ) {}
}
