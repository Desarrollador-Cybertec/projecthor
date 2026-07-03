<?php

declare(strict_types=1);

namespace App\Domains\Projects\Enums;

enum Priority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Baja',
            self::Medium => 'Media',
            self::High => 'Alta',
            self::Urgent => 'Urgente',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Low => 'bg-slate-100 text-slate-700 dark:bg-slate-500/15 dark:text-slate-300',
            self::Medium => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
            self::High => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::Urgent => 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::Low => 1,
            self::Medium => 2,
            self::High => 3,
            self::Urgent => 4,
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
