<?php

declare(strict_types=1);

use App\Domains\Activities\DTOs\ActivityData;
use App\Domains\Activities\Enums\ActivityStatus;
use App\Domains\Activities\Models\Activity;
use App\Domains\Activities\Services\ActivityService;
use App\Domains\Projects\Enums\Priority;
use App\Domains\Projects\Models\Project;
use App\Domains\Stages\Models\Stage;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Users\Models\User;
use App\Livewire\Activities\ActivityList;
use Livewire\Livewire;

it('crea actividades y registra el evento en la línea de tiempo', function () {
    $admin = User::factory()->admin()->create();
    $project = Project::factory()->create(['responsible_id' => $admin->id]);
    $this->actingAs($admin);

    $activity = app(ActivityService::class)->create($project, new ActivityData(
        name: 'Implementar login',
        priority: Priority::High,
        status: ActivityStatus::Pending,
    ), $admin);

    expect($activity->project_id)->toBe($project->id)
        ->and($project->timelineEvents()->where('type', TimelineEventType::ActivityCreated->value)->exists())->toBeTrue();
});

it('recalcula el avance de la etapa al finalizar actividades', function () {
    $admin = User::factory()->admin()->create();
    $project = Project::factory()->create(['responsible_id' => $admin->id]);
    $stage = Stage::factory()->create(['project_id' => $project->id, 'progress' => 0]);
    $activities = Activity::factory()->count(4)->create([
        'project_id' => $project->id,
        'stage_id' => $stage->id,
        'status' => ActivityStatus::Pending,
    ]);
    $this->actingAs($admin);

    app(ActivityService::class)->changeStatus($activities->first(), ActivityStatus::Finished, $admin);

    expect($stage->refresh()->progress)->toBe(25);
});

it('marca la fecha de finalización al completar una actividad', function () {
    $admin = User::factory()->admin()->create();
    $activity = Activity::factory()->create(['status' => ActivityStatus::InReview]);
    $this->actingAs($admin);

    app(ActivityService::class)->changeStatus($activity, ActivityStatus::Finished, $admin);

    expect($activity->refresh())
        ->status->toBe(ActivityStatus::Finished)
        ->completed_at->not->toBeNull();

    expect($activity->project->timelineEvents()->where('type', TimelineEventType::ActivityCompleted->value)->exists())->toBeTrue();
});

it('permite al desarrollador miembro cambiar el estado desde Livewire', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);
    $activity = Activity::factory()->create(['project_id' => $project->id]);

    Livewire::actingAs($developer)
        ->test(ActivityList::class, ['project' => $project])
        ->call('changeStatus', $activity->id, 'in_development')
        ->assertHasNoErrors();

    expect($activity->refresh()->status)->toBe(ActivityStatus::InDevelopment);
});

it('impide a un desarrollador externo gestionar actividades', function () {
    $outsider = User::factory()->create();
    $project = Project::factory()->create();
    $activity = Activity::factory()->create(['project_id' => $project->id]);

    Livewire::actingAs($outsider)
        ->test(ActivityList::class, ['project' => $project])
        ->call('changeStatus', $activity->id, 'finished')
        ->assertForbidden();
});

it('reordena las actividades', function () {
    $admin = User::factory()->admin()->create();
    $project = Project::factory()->create(['responsible_id' => $admin->id]);
    [$first, $second] = Activity::factory()->count(2)->sequence(
        ['position' => 0],
        ['position' => 1],
    )->create(['project_id' => $project->id]);
    $this->actingAs($admin);

    app(ActivityService::class)->reorder($project, [$second->id, $first->id]);

    expect($second->refresh()->position)->toBe(0)
        ->and($first->refresh()->position)->toBe(1);
});
