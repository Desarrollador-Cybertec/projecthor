<?php

declare(strict_types=1);

use App\Domains\Projects\Models\Project;
use App\Domains\Stages\Enums\StageStatus;
use App\Domains\Stages\Models\Stage;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Users\Models\User;
use App\Livewire\Stages\StageList;
use Livewire\Livewire;

it('registra el inicio de una etapa en la línea de tiempo', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);
    $stage = Stage::factory()->create(['project_id' => $project->id, 'status' => StageStatus::Pending]);

    Livewire::actingAs($developer)
        ->test(StageList::class, ['project' => $project])
        ->call('openEdit', $stage->id)
        ->set('status', 'in_progress')
        ->call('save')
        ->assertHasNoErrors();

    expect($stage->refresh())
        ->status->toBe(StageStatus::InProgress)
        ->starts_on->not->toBeNull();

    expect($project->timelineEvents()->where('type', TimelineEventType::StageStarted->value)->exists())->toBeTrue();
});

it('completa una etapa fijando avance 100 y fecha final', function () {
    $admin = User::factory()->admin()->create();
    $project = Project::factory()->create(['responsible_id' => $admin->id]);
    $stage = Stage::factory()->inProgress()->create(['project_id' => $project->id]);

    Livewire::actingAs($admin)
        ->test(StageList::class, ['project' => $project])
        ->call('openEdit', $stage->id)
        ->set('status', 'completed')
        ->call('save')
        ->assertHasNoErrors();

    expect($stage->refresh())
        ->status->toBe(StageStatus::Completed)
        ->progress->toBe(100)
        ->ended_on->not->toBeNull();
});

it('solo el administrador puede crear y eliminar etapas', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);
    $stage = Stage::factory()->create(['project_id' => $project->id]);

    Livewire::actingAs($developer)
        ->test(StageList::class, ['project' => $project])
        ->call('openCreate')
        ->assertForbidden();

    Livewire::actingAs($developer)
        ->test(StageList::class, ['project' => $project])
        ->call('deleteStage', $stage->id)
        ->assertForbidden();
});
