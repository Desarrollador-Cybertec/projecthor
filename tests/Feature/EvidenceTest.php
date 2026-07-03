<?php

declare(strict_types=1);

use App\Domains\Activities\Models\Activity;
use App\Domains\Evidence\Enums\EvidenceType;
use App\Domains\Evidence\Models\Evidence;
use App\Domains\Projects\Models\Project;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Users\Models\User;
use App\Livewire\Activities\ActivityList;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

it('sube archivos como evidencia con miniatura para imágenes desde el detalle de la actividad', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);
    $activity = Activity::factory()->create(['project_id' => $project->id]);

    Livewire::actingAs($developer)
        ->test(ActivityList::class, ['project' => $project])
        ->call('openDetail', $activity->id)
        ->set('evidenceVersion', '1.0')
        ->set('evidenceFiles', [UploadedFile::fake()->image('pantalla.png', 1200, 800)])
        ->call('saveEvidenceFiles')
        ->assertHasNoErrors();

    $evidence = $activity->evidences()->first();

    expect($evidence)->not->toBeNull()
        ->and($evidence->type)->toBe(EvidenceType::Image)
        ->and($evidence->thumbnail_path)->not->toBeNull();

    Storage::disk('public')->assertExists($evidence->file_path);
    Storage::disk('public')->assertExists($evidence->thumbnail_path);
});

it('registra enlaces como evidencia desde el detalle de la actividad', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);
    $activity = Activity::factory()->create(['project_id' => $project->id]);

    Livewire::actingAs($developer)
        ->test(ActivityList::class, ['project' => $project])
        ->call('openDetail', $activity->id)
        ->set('evidenceLinkName', 'Diseño en Figma')
        ->set('evidenceLinkUrl', 'https://figma.com/file/abc')
        ->set('evidenceLinkType', 'figma')
        ->call('saveEvidenceLink')
        ->assertHasNoErrors();

    expect($activity->evidences()->where('type', EvidenceType::Figma->value)->exists())->toBeTrue()
        ->and($project->timelineEvents()->where('type', TimelineEventType::EvidenceUploaded->value)->exists())->toBeTrue();
});

it('impide gestionar evidencias de una actividad de otro proyecto', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);
    $foreignActivity = Activity::factory()->create();

    $this->expectException(ModelNotFoundException::class);

    Livewire::actingAs($developer)
        ->test(ActivityList::class, ['project' => $project])
        ->call('openDetail', $foreignActivity->id);
});

it('impide gestionar evidencias a quien no es miembro', function () {
    $outsider = User::factory()->create();
    $project = Project::factory()->create();
    $activity = Activity::factory()->create(['project_id' => $project->id]);

    Livewire::actingAs($outsider)
        ->test(ActivityList::class, ['project' => $project])
        ->call('openDetail', $activity->id)
        ->set('evidenceLinkName', 'Enlace')
        ->set('evidenceLinkUrl', 'https://example.com')
        ->call('saveEvidenceLink')
        ->assertForbidden();
});

it('descarga una evidencia con autorización', function () {
    $admin = User::factory()->admin()->create();
    $activity = Activity::factory()->create();
    $path = UploadedFile::fake()->create('manual.pdf', 100)->store('evidences');

    $evidence = Evidence::factory()->file()->create([
        'project_id' => $activity->project_id,
        'activity_id' => $activity->id,
        'file_path' => $path,
        'file_name' => 'manual.pdf',
    ]);

    $this->actingAs($admin)
        ->get(route('evidences.download', $evidence))
        ->assertOk()
        ->assertDownload('manual.pdf');
});
