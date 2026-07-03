<?php

declare(strict_types=1);

namespace App\Domains\Activities\Services;

use App\Domains\Activities\DTOs\ActivityData;
use App\Domains\Activities\Enums\ActivityStatus;
use App\Domains\Activities\Events\ActivityCompleted;
use App\Domains\Activities\Events\ActivityCreated;
use App\Domains\Activities\Models\Activity;
use App\Domains\Projects\Models\Project;
use App\Domains\Stages\Models\Stage;
use App\Domains\Users\Models\User;

class ActivityService
{
    public function create(Project $project, ActivityData $data, ?User $actor = null): Activity
    {
        $activity = $project->activities()->create([
            'stage_id' => $data->stageId,
            'name' => $data->name,
            'description' => $data->description,
            'responsible_id' => $data->responsibleId,
            'priority' => $data->priority,
            'status' => $data->status,
            'position' => ((int) $project->activities()->max('position')) + 1,
            'completed_at' => $data->status === ActivityStatus::Finished ? now() : null,
        ]);

        $activity->stage?->recalculateProgress();

        ActivityCreated::dispatch($activity, $actor);

        if ($activity->status === ActivityStatus::Finished) {
            ActivityCompleted::dispatch($activity, $actor);
        }

        return $activity;
    }

    public function update(Activity $activity, ActivityData $data, ?User $actor = null): Activity
    {
        $previousStatus = $activity->status;
        $previousStageId = $activity->stage_id;

        $activity->update([
            'stage_id' => $data->stageId,
            'name' => $data->name,
            'description' => $data->description,
            'responsible_id' => $data->responsibleId,
            'priority' => $data->priority,
            'status' => $data->status,
            'completed_at' => $data->status === ActivityStatus::Finished
                ? ($activity->completed_at ?? now())
                : null,
        ]);

        $this->syncStageProgress($activity, $previousStageId);

        if ($previousStatus !== ActivityStatus::Finished && $data->status === ActivityStatus::Finished) {
            ActivityCompleted::dispatch($activity, $actor);
        }

        return $activity->refresh();
    }

    public function changeStatus(Activity $activity, ActivityStatus $status, ?User $actor = null): Activity
    {
        $previousStatus = $activity->status;

        if ($previousStatus === $status) {
            return $activity;
        }

        $activity->update([
            'status' => $status,
            'completed_at' => $status === ActivityStatus::Finished ? now() : null,
        ]);

        $activity->stage?->recalculateProgress();

        if ($status === ActivityStatus::Finished) {
            ActivityCompleted::dispatch($activity, $actor);
        }

        return $activity->refresh();
    }

    /**
     * Persist a new manual ordering for the given activity ids.
     *
     * @param  list<int>  $orderedIds
     */
    public function reorder(Project $project, array $orderedIds): void
    {
        foreach (array_values($orderedIds) as $position => $id) {
            $project->activities()->whereKey($id)->update(['position' => $position]);
        }
    }

    public function delete(Activity $activity): void
    {
        $stage = $activity->stage;
        $activity->delete();
        $stage?->recalculateProgress();
    }

    private function syncStageProgress(Activity $activity, ?int $previousStageId): void
    {
        $activity->stage?->recalculateProgress();

        if ($previousStageId !== null && $previousStageId !== $activity->stage_id) {
            Stage::query()->find($previousStageId)?->recalculateProgress();
        }
    }
}
