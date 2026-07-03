<?php

declare(strict_types=1);

namespace App\Domains\Stages\DTOs;

use App\Domains\Stages\Enums\StageStatus;

final readonly class StageData
{
    public function __construct(
        public string $name,
        public StageStatus $status,
        public ?string $description = null,
        public ?string $objective = null,
        public int $progress = 0,
        public ?string $startsOn = null,
        public ?string $estimatedEndOn = null,
        public ?string $endedOn = null,
    ) {}
}
