<?php

declare(strict_types=1);

namespace App\Domains\Comments\Enums;

enum CommentStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Abierta',
            self::InProgress => 'En proceso',
            self::Resolved => 'Resuelta',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Open => 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',
            self::InProgress => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::Resolved => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
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
