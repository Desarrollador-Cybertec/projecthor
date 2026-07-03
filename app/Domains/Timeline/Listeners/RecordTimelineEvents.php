<?php

declare(strict_types=1);

namespace App\Domains\Timeline\Listeners;

use App\Domains\Activities\Events\ActivityCompleted;
use App\Domains\Activities\Events\ActivityCreated;
use App\Domains\Comments\Events\CommentAdded;
use App\Domains\Evidence\Events\EvidenceUploaded;
use App\Domains\Files\Events\DocumentUploaded;
use App\Domains\Projects\Events\ProjectCreated;
use App\Domains\Projects\Events\ProjectFinished;
use App\Domains\Screenshots\Events\ScreenshotUploaded;
use App\Domains\Stages\Events\StageCompleted;
use App\Domains\Stages\Events\StageStarted;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Timeline\Services\TimelineRecorder;

class RecordTimelineEvents
{
    public function __construct(
        private TimelineRecorder $recorder,
    ) {}

    /**
     * @return array<class-string, string>
     */
    public function subscribe(): array
    {
        return [
            ProjectCreated::class => 'handleProjectCreated',
            ProjectFinished::class => 'handleProjectFinished',
            StageStarted::class => 'handleStageStarted',
            StageCompleted::class => 'handleStageCompleted',
            ActivityCreated::class => 'handleActivityCreated',
            ActivityCompleted::class => 'handleActivityCompleted',
            EvidenceUploaded::class => 'handleEvidenceUploaded',
            ScreenshotUploaded::class => 'handleScreenshotUploaded',
            CommentAdded::class => 'handleCommentAdded',
            DocumentUploaded::class => 'handleDocumentUploaded',
        ];
    }

    public function handleProjectCreated(ProjectCreated $event): void
    {
        $this->recorder->record(
            $event->project,
            TimelineEventType::ProjectCreated,
            "El proyecto «{$event->project->name}» fue creado.",
            $event->project,
            $event->actor,
        );
    }

    public function handleProjectFinished(ProjectFinished $event): void
    {
        $this->recorder->record(
            $event->project,
            TimelineEventType::ProjectFinished,
            "El proyecto «{$event->project->name}» fue finalizado.",
            $event->project,
            $event->actor,
        );
    }

    public function handleStageStarted(StageStarted $event): void
    {
        $this->recorder->record(
            $event->stage->project,
            TimelineEventType::StageStarted,
            "La etapa «{$event->stage->name}» fue iniciada.",
            $event->stage,
            $event->actor,
        );
    }

    public function handleStageCompleted(StageCompleted $event): void
    {
        $this->recorder->record(
            $event->stage->project,
            TimelineEventType::StageCompleted,
            "La etapa «{$event->stage->name}» fue completada.",
            $event->stage,
            $event->actor,
        );
    }

    public function handleActivityCreated(ActivityCreated $event): void
    {
        $this->recorder->record(
            $event->activity->project,
            TimelineEventType::ActivityCreated,
            "La actividad «{$event->activity->name}» fue creada.",
            $event->activity,
            $event->actor,
        );
    }

    public function handleActivityCompleted(ActivityCompleted $event): void
    {
        $this->recorder->record(
            $event->activity->project,
            TimelineEventType::ActivityCompleted,
            "La actividad «{$event->activity->name}» fue completada.",
            $event->activity,
            $event->actor,
        );
    }

    public function handleEvidenceUploaded(EvidenceUploaded $event): void
    {
        $this->recorder->record(
            $event->evidence->project,
            TimelineEventType::EvidenceUploaded,
            "Se subió la evidencia «{$event->evidence->name}» a la actividad «{$event->evidence->activity->name}».",
            $event->evidence,
            $event->actor,
        );
    }

    public function handleScreenshotUploaded(ScreenshotUploaded $event): void
    {
        $this->recorder->record(
            $event->screenshot->project,
            TimelineEventType::ScreenshotAdded,
            "Se agregó la captura «{$event->screenshot->view_name}».",
            $event->screenshot,
            $event->actor,
        );
    }

    public function handleCommentAdded(CommentAdded $event): void
    {
        $project = $event->comment->resolveProject();

        if ($project === null) {
            return;
        }

        $this->recorder->record(
            $project,
            TimelineEventType::CommentAdded,
            'Se agregó una observación.',
            $event->comment,
            $event->actor,
        );
    }

    public function handleDocumentUploaded(DocumentUploaded $event): void
    {
        $description = $event->isNewVersion
            ? "Se cargó una nueva versión del archivo «{$event->document->name}»."
            : "Se cargó el archivo «{$event->document->name}».";

        $this->recorder->record(
            $event->document->project,
            TimelineEventType::FileUploaded,
            $description,
            $event->document,
            $event->actor,
        );
    }
}
