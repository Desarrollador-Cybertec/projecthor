<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use App\Domains\Users\Notifications\UpcomingDeadlineNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NotifyUpcomingDeadlines extends Command
{
    protected $signature = 'projectflow:notify-upcoming-deadlines
                            {--days=7 : Días de anticipación para avisar}';

    protected $description = 'Notifica al equipo de los proyectos cuya fecha de entrega está próxima.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));

        $projects = Project::query()
            ->where('status', ProjectStatus::Active->value)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays($days)->toDateString()])
            ->with(['members', 'responsible'])
            ->get();

        foreach ($projects as $project) {
            $recipients = $project->team()->filter(fn (User $user): bool => $user->is_active);

            if ($recipients->isEmpty()) {
                continue;
            }

            Notification::send($recipients, new UpcomingDeadlineNotification($project));
        }

        $this->info("Se notificaron {$projects->count()} proyectos con entregas próximas.");

        return self::SUCCESS;
    }
}
