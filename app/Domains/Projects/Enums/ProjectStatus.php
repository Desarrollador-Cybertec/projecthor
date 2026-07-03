<?php

declare(strict_types=1);

namespace App\Domains\Projects\Enums;

enum ProjectStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Paused => 'Pausado',
            self::Completed => 'Finalizado',
            self::Cancelled => 'Cancelado',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Active => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::Paused => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::Completed => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
            self::Cancelled => 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',
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
