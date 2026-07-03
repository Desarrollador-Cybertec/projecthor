<?php

declare(strict_types=1);

namespace App\Domains\Evidence\Events;

use App\Domains\Evidence\Models\Evidence;
use App\Domains\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class EvidenceUploaded
{
    use Dispatchable;

    public function __construct(
        public Evidence $evidence,
        public ?User $actor = null,
    ) {}
}
