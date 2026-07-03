<?php

declare(strict_types=1);

namespace App\Domains\Screenshots\DTOs;

final readonly class ScreenshotData
{
    public function __construct(
        public string $viewName,
        public ?string $module = null,
        public ?string $resolution = null,
        public ?string $platform = null,
        public ?string $description = null,
        public ?string $notes = null,
        public ?string $version = null,
        public ?string $takenAt = null,
        public ?int $stageId = null,
        public ?int $activityId = null,
    ) {}
}
