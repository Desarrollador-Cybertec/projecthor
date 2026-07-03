<?php

declare(strict_types=1);

namespace App\Domains\Users\Listeners;

use App\Domains\Activities\Events\ActivityCompleted;
use App\Domains\Comments\Events\CommentAdded;
use App\Domains\Evidence\Events\EvidenceUploaded;
use App\Domains\Files\Events\DocumentUploaded;
use App\Domains\Projects\Events\ProjectCreated;
use App\Domains\Projects\Events\ProjectFinished;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use App\Domains\Users\Notifications\ProjectEventNotification;
use Illuminate\Support\Facades\Notification;

class SendProjectNotifications
{
    /**
     * @return array<class-string, string>
     */
    public function subscribe(): array
    {
        return [
            ProjectCreated::class => 'handleProjectCreated',
            ProjectFinished::class => 'handleProjectFinished',
            ActivityCompleted::class => 'handleActivityCompleted',
            CommentAdded::class => 'handleCommentAdded',
            EvidenceUploaded::class => 'handleEvidenceUploaded',
            DocumentUploaded::class => 'handleDocumentUploaded',
        ];
    }

    public function handleProjectCreated(ProjectCreated $event): void
    {
        $this->notifyTeam(
            $event->project,
            $event->actor,
            'Nuevo proyecto',
            "Fuiste asignado al proyecto «{$event->project->name}».",
        );
    }

    public function handleProjectFinished(ProjectFinished $event): void
    {
        $this->notifyTeam(
            $event->project,
            $event->actor,
            'Proyecto finalizado',
            "El proyecto «{$event->project->name}» fue finalizado.",
        );
    }

    public function handleActivityCompleted(ActivityCompleted $event): void
    {
        $this->notifyTeam(
            $event->activity->project,
            $event->actor,
            'Actividad completada',
            "La actividad «{$event->activity->name}» fue completada en «{$event->activity->project->name}».",
        );
    }

    public function handleCommentAdded(CommentAdded $event): void
    {
        $project = $event->comment->resolveProject();

        if ($project === null) {
            return;
        }

        $this->notifyTeam(
            $project,
            $event->actor,
            'Nueva observación',
            "{$event->comment->author->name} agregó una observación en «{$project->name}».",
        );
    }

    public function handleEvidenceUploaded(EvidenceUploaded $event): void
    {
        $this->notifyTeam(
            $event->evidence->project,
            $event->actor,
            'Nueva evidencia',
            "Se subió la evidencia «{$event->evidence->name}» en «{$event->evidence->project->name}».",
        );
    }

    public function handleDocumentUploaded(DocumentUploaded $event): void
    {
        $message = $event->isNewVersion
            ? "Se cargó una nueva versión de «{$event->document->name}» en «{$event->document->project->name}»."
            : "Se cargó el archivo «{$event->document->name}» en «{$event->document->project->name}».";

        $this->notifyTeam($event->document->project, $event->actor, 'Nuevo archivo', $message);
    }

    private function notifyTeam(Project $project, ?User $actor, string $title, string $message): void
    {
        $recipients = $project->team()
            ->reject(fn (User $user): bool => $actor !== null && $user->id === $actor->id)
            ->filter(fn (User $user): bool => $user->is_active);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new ProjectEventNotification($title, $message, $project));
    }
}
