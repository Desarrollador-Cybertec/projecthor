<?php

declare(strict_types=1);

use App\Domains\Activities\Models\Activity;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;

it('exporta el listado de proyectos a Excel', function () {
    $admin = User::factory()->admin()->create();
    Project::factory()->count(2)->create(['responsible_id' => $admin->id]);

    $response = $this->actingAs($admin)->get(route('projects.export.xlsx'));

    $response->assertOk();
    expect($response->headers->get('content-type'))
        ->toContain('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('exporta las actividades de un proyecto a Excel', function () {
    $admin = User::factory()->admin()->create();
    $project = Project::factory()->create(['responsible_id' => $admin->id]);
    Activity::factory()->count(3)->create(['project_id' => $project->id]);

    $this->actingAs($admin)
        ->get(route('projects.activities.export.xlsx', $project))
        ->assertOk()
        ->assertDownload('actividades-'.$project->slug.'.xlsx');
});

it('exporta el reporte del proyecto en PDF', function () {
    $admin = User::factory()->admin()->create();
    $project = Project::factory()->create(['responsible_id' => $admin->id]);
    Activity::factory()->count(2)->create(['project_id' => $project->id]);

    $response = $this->actingAs($admin)->get(route('projects.export.pdf', $project));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('impide exportar proyectos ajenos', function () {
    $outsider = User::factory()->create();
    $project = Project::factory()->create();

    $this->actingAs($outsider)
        ->get(route('projects.export.pdf', $project))
        ->assertForbidden();
});
