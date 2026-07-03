<?php

declare(strict_types=1);

namespace App\Domains\Users\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Developer = 'developer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Developer => 'Desarrollador',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Admin => 'bg-violet-100 text-violet-800 dark:bg-violet-500/15 dark:text-violet-300',
            self::Developer => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])
            ->all();
    }
}
