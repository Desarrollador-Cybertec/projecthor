<?php

declare(strict_types=1);

namespace App\Domains\Timeline\Enums;

enum TimelineEventType: string
{
    case ProjectCreated = 'project_created';
    case StageStarted = 'stage_started';
    case StageCompleted = 'stage_completed';
    case ActivityCreated = 'activity_created';
    case ActivityCompleted = 'activity_completed';
    case EvidenceUploaded = 'evidence_uploaded';
    case ScreenshotAdded = 'screenshot_added';
    case CommentAdded = 'comment_added';
    case FileUploaded = 'file_uploaded';
    case ProjectFinished = 'project_finished';

    public function label(): string
    {
        return match ($this) {
            self::ProjectCreated => 'Proyecto creado',
            self::StageStarted => 'Etapa iniciada',
            self::StageCompleted => 'Etapa completada',
            self::ActivityCreated => 'Actividad creada',
            self::ActivityCompleted => 'Actividad completada',
            self::EvidenceUploaded => 'Evidencia subida',
            self::ScreenshotAdded => 'Captura agregada',
            self::CommentAdded => 'Observación agregada',
            self::FileUploaded => 'Archivo cargado',
            self::ProjectFinished => 'Proyecto finalizado',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ProjectCreated => 'M12 4.5v15m7.5-7.5h-15',
            self::StageStarted => 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z',
            self::StageCompleted => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            self::ActivityCreated => 'M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            self::ActivityCompleted => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            self::EvidenceUploaded => 'M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5',
            self::ScreenshotAdded => 'M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z',
            self::CommentAdded => 'M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z',
            self::FileUploaded => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
            self::ProjectFinished => 'M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0',
        };
    }

    public function iconClasses(): string
    {
        return match ($this) {
            self::ProjectCreated, self::ProjectFinished => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400',
            self::StageStarted, self::StageCompleted => 'bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400',
            self::ActivityCreated, self::ActivityCompleted => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400',
            self::EvidenceUploaded, self::ScreenshotAdded => 'bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400',
            self::CommentAdded => 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
            self::FileUploaded => 'bg-cyan-100 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-400',
        };
    }
}
