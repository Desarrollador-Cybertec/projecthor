<?php

declare(strict_types=1);

use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use App\Domains\Users\Notifications\ProjectEventNotification;
use App\Domains\Users\Notifications\UpcomingDeadlineNotification;
use App\Livewire\Notifications\Bell;
use App\Livewire\Search\GlobalSearch;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('la búsqueda global encuentra proyectos visibles', function () {
    $admin = User::factory()->admin()->create();
    $project = Project::factory()->create(['name' => 'Plataforma Inventario', 'responsible_id' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(GlobalSearch::class)
        ->set('query', 'Inventario')
        ->assertSee('Plataforma Inventario');

    expect($project->name)->toContain('Inventario');
});

it('la búsqueda global no expone proyectos ajenos', function () {
    $developer = User::factory()->create();
    Project::factory()->create(['name' => 'Proyecto Secreto']);

    Livewire::actingAs($developer)
        ->test(GlobalSearch::class)
        ->set('query', 'Secreto')
        ->assertDontSee('Proyecto Secreto');
});

it('marca notificaciones como leídas', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['responsible_id' => $user->id]);

    $user->notify(new ProjectEventNotification('Prueba', 'Mensaje de prueba', $project));

    expect($user->unreadNotifications()->count())->toBe(1);

    Livewire::actingAs($user)
        ->test(Bell::class)
        ->call('markAllAsRead');

    expect($user->refresh()->unreadNotifications()->count())->toBe(0);
});

it('el comando de entregas próximas notifica al equipo', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $developer = User::factory()->create();

    $dueSoon = Project::factory()->create([
        'responsible_id' => $admin->id,
        'due_date' => now()->addDays(3)->toDateString(),
    ]);
    $dueSoon->members()->attach($developer);

    // Proyecto con entrega lejana: no debe notificar.
    Project::factory()->create([
        'responsible_id' => $admin->id,
        'due_date' => now()->addMonths(3)->toDateString(),
    ]);

    $this->artisan('projectflow:notify-upcoming-deadlines')->assertSuccessful();

    Notification::assertSentTo($developer, UpcomingDeadlineNotification::class);
    Notification::assertSentToTimes($admin, UpcomingDeadlineNotification::class, 1);
});
