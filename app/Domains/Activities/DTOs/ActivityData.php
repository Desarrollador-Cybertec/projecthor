<?php

declare(strict_types=1);

namespace App\Domains\Activities\DTOs;

use App\Domains\Activities\Enums\ActivityStatus;
use App\Domains\Projects\Enums\Priority;

final readonly class ActivityData
{
    public function __construct(
        public string $name,
        public Priority $priority,
        public ActivityStatus $status,
        public ?string $description = null,
        public ?int $stageId = null,
        public ?int $responsibleId = null,
    ) {}
}
