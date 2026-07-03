<?php

declare(strict_types=1);

namespace App\Domains\Activities\Enums;

enum ActivityStatus: string
{
    case Pending = 'pending';
    case InDevelopment = 'in_development';
    case InReview = 'in_review';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::InDevelopment => 'En desarrollo',
            self::InReview => 'En revisión',
            self::Finished => 'Finalizada',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-slate-100 text-slate-700 dark:bg-slate-500/15 dark:text-slate-300',
            self::InDevelopment => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
            self::InReview => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::Finished => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
        };
    }

    public function isFinished(): bool
    {
        return $this === self::Finished;
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])
            ->all();
    }
}
