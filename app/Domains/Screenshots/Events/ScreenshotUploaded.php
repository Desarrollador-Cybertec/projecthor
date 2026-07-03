<?php

declare(strict_types=1);

namespace App\Domains\Screenshots\Events;

use App\Domains\Screenshots\Models\Screenshot;
use App\Domains\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class ScreenshotUploaded
{
    use Dispatchable;

    public function __construct(
        public Screenshot $screenshot,
        public ?User $actor = null,
    ) {}
}
