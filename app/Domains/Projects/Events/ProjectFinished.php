<?php

declare(strict_types=1);

namespace App\Domains\Projects\Events;

use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class ProjectFinished
{
    use Dispatchable;

    public function __construct(
        public Project $project,
        public ?User $actor = null,
    ) {}
}
