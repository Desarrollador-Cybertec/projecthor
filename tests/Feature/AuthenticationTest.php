<?php

declare(strict_types=1);

use App\Domains\Users\Models\User;

it('muestra la pantalla de login', function () {
    $this->get('/login')->assertOk()->assertSee('Iniciar sesión');
});

it('autentica con credenciales válidas', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rechaza credenciales inválidas', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'incorrecta',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('rechaza usuarios inactivos', function () {
    $user = User::factory()->inactive()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('cierra la sesión', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));

    $this->assertGuest();
});

it('redirige a invitados al login', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
});
