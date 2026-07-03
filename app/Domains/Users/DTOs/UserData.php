<?php

declare(strict_types=1);

namespace App\Domains\Users\DTOs;

use App\Domains\Users\Enums\UserRole;

final readonly class UserData
{
    public function __construct(
        public string $name,
        public string $email,
        public UserRole $role,
        public bool $isActive = true,
        public ?string $password = null,
    ) {}
}
