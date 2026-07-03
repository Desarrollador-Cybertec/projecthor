<?php

declare(strict_types=1);

namespace App\Domains\Timeline\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Timeline\Models\TimelineEvent;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Model;

class TimelineRecorder
{
    public function record(
        Project $project,
        TimelineEventType $type,
        string $description,
        ?Model $subject = null,
        ?User $user = null,
    ): TimelineEvent {
        return TimelineEvent::query()->create([
            'project_id' => $project->id,
            'user_id' => $user?->id,
            'type' => $type,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
