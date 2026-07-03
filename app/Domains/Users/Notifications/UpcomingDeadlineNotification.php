<?php

declare(strict_types=1);

namespace App\Domains\Users\Notifications;

use App\Domains\Projects\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UpcomingDeadlineNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Project $project,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $dueDate = $this->project->due_date?->translatedFormat('d \d\e F \d\e Y');

        return [
            'title' => 'Entrega próxima',
            'message' => "El proyecto «{$this->project->name}» tiene fecha de entrega el {$dueDate}.",
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'url' => route('projects.show', $this->project, absolute: false),
        ];
    }
}
