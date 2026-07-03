<?php

declare(strict_types=1);

namespace App\Domains\Files\Events;

use App\Domains\Files\Models\Document;
use App\Domains\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class DocumentUploaded
{
    use Dispatchable;

    public function __construct(
        public Document $document,
        public ?User $actor = null,
        public bool $isNewVersion = false,
    ) {}
}
