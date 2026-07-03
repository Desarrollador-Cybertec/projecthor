<?php

declare(strict_types=1);

namespace App\Domains\Stages\Enums;

enum StageStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case InReview = 'in_review';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::InProgress => 'En proceso',
            self::InReview => 'En revisión',
            self::Completed => 'Completada',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-slate-100 text-slate-700 dark:bg-slate-500/15 dark:text-slate-300',
            self::InProgress => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
            self::InReview => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::Completed => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
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
