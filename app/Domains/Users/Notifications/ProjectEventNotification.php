<?php

declare(strict_types=1);

namespace App\Domains\Users\Notifications;

use App\Domains\Projects\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProjectEventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public Project $project,
        public ?string $url = null,
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
        return [
            'title' => $this->title,
            'message' => $this->message,
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'url' => $this->url ?? route('projects.show', $this->project, absolute: false),
        ];
    }
}
