<?php

declare(strict_types=1);

namespace App\Domains\Stages\Events;

use App\Domains\Stages\Models\Stage;
use App\Domains\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class StageCompleted
{
    use Dispatchable;

    public function __construct(
        public Stage $stage,
        public ?User $actor = null,
    ) {}
}
