<?php

declare(strict_types=1);

use App\Domains\Projects\Models\Project;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Users\Models\User;
use App\Livewire\Screenshots\ScreenshotGallery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

it('sube capturas con miniatura y registra el evento', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);

    Livewire::actingAs($developer)
        ->test(ScreenshotGallery::class, ['project' => $project])
        ->set('image', UploadedFile::fake()->image('dashboard.png', 1600, 900))
        ->set('view_name', 'Dashboard')
        ->set('module', 'Reportes')
        ->set('resolution', '1600x900')
        ->set('version', '1.0')
        ->call('save')
        ->assertHasNoErrors();

    $screenshot = $project->screenshots()->first();

    expect($screenshot)->not->toBeNull()
        ->and($screenshot->thumbnail_path)->not->toBeNull()
        ->and($project->timelineEvents()->where('type', TimelineEventType::ScreenshotAdded->value)->exists())->toBeTrue();

    Storage::disk('public')->assertExists($screenshot->image_path);
    Storage::disk('public')->assertExists($screenshot->thumbnail_path);
});

it('exige una imagen válida', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);

    Livewire::actingAs($developer)
        ->test(ScreenshotGallery::class, ['project' => $project])
        ->set('image', UploadedFile::fake()->create('archivo.pdf', 100, 'application/pdf'))
        ->set('view_name', 'Dashboard')
        ->call('save')
        ->assertHasErrors('image');
});

it('impide subir capturas a quien no es miembro', function () {
    $outsider = User::factory()->create();
    $project = Project::factory()->create();

    Livewire::actingAs($outsider)
        ->test(ScreenshotGallery::class, ['project' => $project])
        ->call('openUpload')
        ->assertForbidden();
});
