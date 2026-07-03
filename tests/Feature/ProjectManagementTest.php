<?php

declare(strict_types=1);

use App\Domains\Projects\DTOs\ProjectData;
use App\Domains\Projects\Enums\Priority;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\ProjectService;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Users\Models\User;
use App\Domains\Users\Notifications\ProjectEventNotification;
use App\Livewire\Projects\CreateProject;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

function makeProjectData(User $responsible, array $memberIds = []): ProjectData
{
    return new ProjectData(
        name: 'Proyecto de prueba',
        clientName: 'Cliente de prueba',
        responsibleId: $responsible->id,
        priority: Priority::High,
        status: ProjectStatus::Active,
        memberIds: $memberIds,
    );
}

it('crea un proyecto con sus seis etapas por defecto', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $project = app(ProjectService::class)->create(makeProjectData($admin), actor: $admin);

    expect($project->stages)->toHaveCount(6)
        ->and($project->stages->pluck('name')->all())
        ->toBe(['Planeación', 'Diseño', 'Desarrollo', 'Pruebas', 'Implementación', 'Finalizado']);
});

it('registra el evento de creación en la línea de tiempo', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $project = app(ProjectService::class)->create(makeProjectData($admin), actor: $admin);

    expect($project->timelineEvents()->where('type', TimelineEventType::ProjectCreated->value)->exists())->toBeTrue();
});

it('notifica al equipo cuando se crea un proyecto', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $developer = User::factory()->create();
    $this->actingAs($admin);

    app(ProjectService::class)->create(makeProjectData($admin, [$developer->id]), actor: $admin);

    Notification::assertSentTo($developer, ProjectEventNotification::class);
    Notification::assertNotSentTo($admin, ProjectEventNotification::class);
});

it('permite crear proyectos desde el componente Livewire', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CreateProject::class)
        ->set('form.name', 'Sistema de Facturación')
        ->set('form.client_name', 'Acme')
        ->set('form.responsible_id', $admin->id)
        ->set('form.priority', 'high')
        ->set('form.status', 'active')
        ->call('save')
        ->assertHasNoErrors();

    expect(Project::query()->where('name', 'Sistema de Facturación')->exists())->toBeTrue();
});

it('valida los campos obligatorios del proyecto', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CreateProject::class)
        ->set('form.name', '')
        ->set('form.client_name', '')
        ->call('save')
        ->assertHasErrors(['form.name', 'form.client_name']);
});

it('impide a los desarrolladores crear proyectos', function () {
    $developer = User::factory()->create();

    $this->actingAs($developer)->get('/projects/create')->assertForbidden();
});

it('finaliza un proyecto y registra el evento', function () {
    $admin = User::factory()->admin()->create();
    $project = Project::factory()->create(['responsible_id' => $admin->id]);
    $this->actingAs($admin);

    app(ProjectService::class)->finish($project, $admin);

    expect($project->refresh())
        ->status->toBe(ProjectStatus::Completed)
        ->finished_at->not->toBeNull()
        ->and($project->timelineEvents()->where('type', TimelineEventType::ProjectFinished->value)->exists())->toBeTrue();
});

it('impide ver un proyecto ajeno', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();

    $this->actingAs($developer)->get(route('projects.show', $project))->assertForbidden();
});

it('permite al miembro ver su proyecto', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);

    $this->actingAs($developer)->get(route('projects.show', $project))->assertOk();
});

it('elimina proyectos con soft delete', function () {
    $admin = User::factory()->admin()->create();
    $project = Project::factory()->create(['responsible_id' => $admin->id]);
    $this->actingAs($admin);

    app(ProjectService::class)->delete($project);

    $this->assertSoftDeleted('projects', ['id' => $project->id]);
});

it('audita los cambios del proyecto', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $project = Project::factory()->create(['responsible_id' => $admin->id]);
    $project->update(['name' => 'Nombre cambiado']);

    $audit = $project->audits()->where('event', 'updated')->first();

    expect($audit)->not->toBeNull()
        ->and($audit->new_values['name'])->toBe('Nombre cambiado')
        ->and($audit->user_id)->toBe($admin->id);
});
