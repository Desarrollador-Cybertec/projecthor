<?php

declare(strict_types=1);

namespace App\Domains\Projects\DTOs;

use App\Domains\Projects\Enums\Priority;
use App\Domains\Projects\Enums\ProjectStatus;

final readonly class ProjectData
{
    /**
     * @param  list<int>  $memberIds
     */
    public function __construct(
        public string $name,
        public string $clientName,
        public int $responsibleId,
        public Priority $priority,
        public ProjectStatus $status,
        public ?string $description = null,
        public ?string $color = null,
        public ?string $startDate = null,
        public ?string $dueDate = null,
        public ?string $productionUrl = null,
        public ?string $stagingUrl = null,
        public ?string $documentationUrl = null,
        public ?string $repositoryUrl = null,
        public array $memberIds = [],
    ) {}
}
