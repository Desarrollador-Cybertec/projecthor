<?php

declare(strict_types=1);

use App\Domains\Users\Enums\UserRole;
use App\Domains\Users\Models\User;
use App\Livewire\Users\Index;
use Livewire\Livewire;

it('impide el acceso a desarrolladores', function () {
    $developer = User::factory()->create();

    $this->actingAs($developer)->get('/users')->assertForbidden();
});

it('permite al administrador crear usuarios', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('openCreate')
        ->set('name', 'Nuevo Desarrollador')
        ->set('email', 'nuevo@projectflow.test')
        ->set('password', 'contraseña-segura')
        ->set('role', 'developer')
        ->call('save')
        ->assertHasNoErrors();

    $user = User::query()->where('email', 'nuevo@projectflow.test')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::Developer)
        ->and($user->is_active)->toBeTrue();
});

it('valida correos duplicados', function () {
    $admin = User::factory()->admin()->create();
    $existing = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('openCreate')
        ->set('name', 'Duplicado')
        ->set('email', $existing->email)
        ->set('password', 'contraseña-segura')
        ->call('save')
        ->assertHasErrors('email');
});

it('actualiza usuarios sin cambiar la contraseña si se deja en blanco', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $originalPassword = $user->password;

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('openEdit', $user->id)
        ->set('name', 'Nombre Actualizado')
        ->call('save')
        ->assertHasNoErrors();

    expect($user->refresh())
        ->name->toBe('Nombre Actualizado')
        ->password->toBe($originalPassword);
});

it('impide desactivar la propia cuenta', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('toggleActive', $admin->id);

    expect($admin->refresh()->is_active)->toBeTrue();
});

it('elimina usuarios con soft delete', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('deleteUser', $user->id)
        ->assertHasNoErrors();

    $this->assertSoftDeleted('users', ['id' => $user->id]);
});
