<?php

declare(strict_types=1);

use App\Domains\Activities\Models\Activity;
use App\Domains\Evidence\Enums\EvidenceType;
use App\Domains\Evidence\Models\Evidence;
use App\Domains\Projects\Models\Project;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Users\Models\User;
use App\Livewire\Evidence\EvidenceManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

it('sube archivos como evidencia con miniatura para imágenes', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);
    $activity = Activity::factory()->create(['project_id' => $project->id]);

    Livewire::actingAs($developer)
        ->test(EvidenceManager::class, ['project' => $project])
        ->set('upload_activity_id', $activity->id)
        ->set('upload_version', '1.0')
        ->set('files', [UploadedFile::fake()->image('pantalla.png', 1200, 800)])
        ->call('saveFiles')
        ->assertHasNoErrors();

    $evidence = $activity->evidences()->first();

    expect($evidence)->not->toBeNull()
        ->and($evidence->type)->toBe(EvidenceType::Image)
        ->and($evidence->thumbnail_path)->not->toBeNull();

    Storage::disk('public')->assertExists($evidence->file_path);
    Storage::disk('public')->assertExists($evidence->thumbnail_path);
});

it('registra enlaces como evidencia', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);
    $activity = Activity::factory()->create(['project_id' => $project->id]);

    Livewire::actingAs($developer)
        ->test(EvidenceManager::class, ['project' => $project])
        ->set('link_activity_id', $activity->id)
        ->set('link_name', 'Diseño en Figma')
        ->set('link_url', 'https://figma.com/file/abc')
        ->set('link_type', 'figma')
        ->call('saveLink')
        ->assertHasNoErrors();

    expect($activity->evidences()->where('type', EvidenceType::Figma->value)->exists())->toBeTrue()
        ->and($project->timelineEvents()->where('type', TimelineEventType::EvidenceUploaded->value)->exists())->toBeTrue();
});

it('valida que la actividad pertenezca al proyecto', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);
    $foreignActivity = Activity::factory()->create();

    Livewire::actingAs($developer)
        ->test(EvidenceManager::class, ['project' => $project])
        ->set('link_activity_id', $foreignActivity->id)
        ->set('link_name', 'Enlace')
        ->set('link_url', 'https://example.com')
        ->call('saveLink')
        ->assertHasErrors('link_activity_id');
});

it('impide subir evidencias a quien no es miembro', function () {
    $outsider = User::factory()->create();
    $project = Project::factory()->create();

    Livewire::actingAs($outsider)
        ->test(EvidenceManager::class, ['project' => $project])
        ->call('openUpload')
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
