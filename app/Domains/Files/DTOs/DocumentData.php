<?php

declare(strict_types=1);

namespace App\Domains\Files\DTOs;

use App\Domains\Files\Enums\DocumentCategory;

final readonly class DocumentData
{
    public function __construct(
        public string $name,
        public DocumentCategory $category,
        public ?string $description = null,
        public ?string $notes = null,
    ) {}
}
