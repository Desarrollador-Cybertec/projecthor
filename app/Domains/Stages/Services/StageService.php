<?php

declare(strict_types=1);

namespace App\Domains\Stages\Services;

use App\Domains\Stages\DTOs\StageData;
use App\Domains\Stages\Enums\StageStatus;
use App\Domains\Stages\Events\StageCompleted;
use App\Domains\Stages\Events\StageStarted;
use App\Domains\Stages\Models\Stage;
use App\Domains\Users\Models\User;

class StageService
{
    public function update(Stage $stage, StageData $data, ?User $actor = null): Stage
    {
        $previousStatus = $stage->status;

        $attributes = [
            'name' => $data->name,
            'description' => $data->description,
            'objective' => $data->objective,
            'status' => $data->status,
            'progress' => max(0, min(100, $data->progress)),
            'starts_on' => $data->startsOn,
            'estimated_end_on' => $data->estimatedEndOn,
            'ended_on' => $data->endedOn,
        ];

        if ($data->status === StageStatus::InProgress && $data->startsOn === null) {
            $attributes['starts_on'] = now()->toDateString();
        }

        if ($data->status === StageStatus::Completed) {
            $attributes['progress'] = 100;
            $attributes['ended_on'] = $data->endedOn ?? now()->toDateString();
        }

        $stage->update($attributes);

        if ($previousStatus !== $data->status) {
            if ($data->status === StageStatus::InProgress && $previousStatus === StageStatus::Pending) {
                StageStarted::dispatch($stage, $actor);
            }

            if ($data->status === StageStatus::Completed) {
                StageCompleted::dispatch($stage, $actor);
            }
        }

        return $stage->refresh();
    }
}
