<?php

declare(strict_types=1);

namespace App\Domains\Comments\DTOs;

final readonly class CommentData
{
    public function __construct(
        public string $content,
        public ?int $parentId = null,
    ) {}
}
