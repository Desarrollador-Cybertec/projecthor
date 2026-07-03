<?php

declare(strict_types=1);

use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;

it('muestra el dashboard con indicadores', function () {
    $admin = User::factory()->admin()->create();
    Project::factory()->count(2)->create(['responsible_id' => $admin->id]);
    Project::factory()->completed()->create(['responsible_id' => $admin->id]);

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Proyectos activos')
        ->assertSee('Próximas entregas');
});

it('solo muestra proyectos visibles al desarrollador', function () {
    $developer = User::factory()->create();
    $visible = Project::factory()->create();
    $visible->members()->attach($developer);
    $hidden = Project::factory()->create();

    $this->actingAs($developer)
        ->get('/projects')
        ->assertOk()
        ->assertSee($visible->name)
        ->assertDontSee($hidden->name);
});
