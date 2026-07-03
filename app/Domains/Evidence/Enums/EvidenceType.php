<?php

declare(strict_types=1);

namespace App\Domains\Evidence\Enums;

enum EvidenceType: string
{
    case Image = 'image';
    case Video = 'video';
    case Document = 'document';
    case Pdf = 'pdf';
    case Zip = 'zip';
    case Link = 'link';
    case Figma = 'figma';
    case Production = 'production';

    public function label(): string
    {
        return match ($this) {
            self::Image => 'Imagen',
            self::Video => 'Video',
            self::Document => 'Documento',
            self::Pdf => 'PDF',
            self::Zip => 'ZIP',
            self::Link => 'Enlace',
            self::Figma => 'Figma',
            self::Production => 'Producción',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Image => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::Video => 'bg-violet-100 text-violet-800 dark:bg-violet-500/15 dark:text-violet-300',
            self::Document => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
            self::Pdf => 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',
            self::Zip => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::Link => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-500/15 dark:text-cyan-300',
            self::Figma => 'bg-fuchsia-100 text-fuchsia-800 dark:bg-fuchsia-500/15 dark:text-fuchsia-300',
            self::Production => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-300',
        };
    }

    public function isLinkBased(): bool
    {
        return in_array($this, [self::Link, self::Figma, self::Production], true);
    }

    /**
     * Deduce the evidence type from an uploaded file's mime type and extension.
     */
    public static function fromFile(string $mimeType, string $extension): self
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => self::Image,
            str_starts_with($mimeType, 'video/') => self::Video,
            $mimeType === 'application/pdf', strtolower($extension) === 'pdf' => self::Pdf,
            in_array(strtolower($extension), ['zip', 'rar', '7z'], true) => self::Zip,
            default => self::Document,
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
