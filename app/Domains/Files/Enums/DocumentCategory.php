<?php

declare(strict_types=1);

namespace App\Domains\Files\Enums;

enum DocumentCategory: string
{
    case Contracts = 'contracts';
    case Manuals = 'manuals';
    case Mockups = 'mockups';
    case Branding = 'branding';
    case Minutes = 'minutes';
    case TechnicalDocs = 'technical_docs';
    case Resources = 'resources';

    public function label(): string
    {
        return match ($this) {
            self::Contracts => 'Contratos',
            self::Manuals => 'Manuales',
            self::Mockups => 'Mockups',
            self::Branding => 'Branding',
            self::Minutes => 'Actas',
            self::TechnicalDocs => 'Documentación técnica',
            self::Resources => 'Recursos',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Contracts => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-300',
            self::Manuals => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
            self::Mockups => 'bg-fuchsia-100 text-fuchsia-800 dark:bg-fuchsia-500/15 dark:text-fuchsia-300',
            self::Branding => 'bg-violet-100 text-violet-800 dark:bg-violet-500/15 dark:text-violet-300',
            self::Minutes => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::TechnicalDocs => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::Resources => 'bg-slate-100 text-slate-700 dark:bg-slate-500/15 dark:text-slate-300',
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
