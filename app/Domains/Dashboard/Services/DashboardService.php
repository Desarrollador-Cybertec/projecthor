<?php

declare(strict_types=1);

namespace App\Domains\Dashboard\Services;

use App\Domains\Activities\Enums\ActivityStatus;
use App\Domains\Activities\Models\Activity;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Timeline\Models\TimelineEvent;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DashboardService
{
    /**
     * @return array{
     *     activeProjects: int,
     *     finishedProjects: int,
     *     pendingActivities: int,
     *     finishedActivities: int,
     *     upcomingDeliveries: Collection<int, Project>,
     *     lastEvent: TimelineEvent|null,
     * }
     */
    public function overview(User $user): array
    {
        return [
            'activeProjects' => $this->projectsFor($user)->where('status', ProjectStatus::Active->value)->count(),
            'finishedProjects' => $this->projectsFor($user)->where('status', ProjectStatus::Completed->value)->count(),
            'pendingActivities' => $this->activitiesFor($user)
                ->whereNot('status', ActivityStatus::Finished->value)
                ->count(),
            'finishedActivities' => $this->activitiesFor($user)
                ->where('status', ActivityStatus::Finished->value)
                ->count(),
            'upcomingDeliveries' => $this->projectsFor($user)
                ->where('status', ProjectStatus::Active->value)
                ->whereNotNull('due_date')
                ->whereBetween('due_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
                ->orderBy('due_date')
                ->limit(5)
                ->get(),
            'lastEvent' => TimelineEvent::query()
                ->whereIn('project_id', $this->projectsFor($user)->select('id'))
                ->with(['project', 'user'])
                ->latest('created_at')
                ->first(),
        ];
    }

    /**
     * @return Builder<Project>
     */
    private function projectsFor(User $user): Builder
    {
        return Project::query()->visibleTo($user);
    }

    /**
     * @return Builder<Activity>
     */
    private function activitiesFor(User $user): Builder
    {
        return Activity::query()->whereIn('project_id', $this->projectsFor($user)->select('id'));
    }
}
