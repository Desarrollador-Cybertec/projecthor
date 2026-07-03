<?php

declare(strict_types=1);

namespace App\Domains\Activities\Events;

use App\Domains\Activities\Models\Activity;
use App\Domains\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class ActivityCompleted
{
    use Dispatchable;

    public function __construct(
        public Activity $activity,
        public ?User $actor = null,
    ) {}
}
