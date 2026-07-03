<?php

declare(strict_types=1);

namespace App\Domains\Evidence\DTOs;

use App\Domains\Evidence\Enums\EvidenceType;

final readonly class EvidenceData
{
    public function __construct(
        public string $name,
        public string $version = '1.0',
        public ?string $description = null,
        public ?EvidenceType $type = null,
        public ?string $url = null,
    ) {}
}
