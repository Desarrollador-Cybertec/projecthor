<?php

declare(strict_types=1);

use App\Domains\Files\Models\Document;
use App\Domains\Projects\Models\Project;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Users\Models\User;
use App\Livewire\Files\DocumentLibrary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

it('permite al administrador cargar archivos con versión inicial', function () {
    $admin = User::factory()->admin()->create();
    $project = Project::factory()->create(['responsible_id' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(DocumentLibrary::class, ['project' => $project])
        ->set('file', UploadedFile::fake()->create('contrato.pdf', 500, 'application/pdf'))
        ->set('name', 'Contrato de desarrollo')
        ->set('category', 'contracts')
        ->call('save')
        ->assertHasNoErrors();

    $document = $project->documents()->first();

    expect($document)->not->toBeNull()
        ->and($document->versions)->toHaveCount(1)
        ->and($document->latestVersion->version)->toBe(1)
        ->and($project->timelineEvents()->where('type', TimelineEventType::FileUploaded->value)->exists())->toBeTrue();

    Storage::disk('public')->assertExists($document->latestVersion->file_path);
});

it('versiona los documentos de forma incremental', function () {
    $admin = User::factory()->admin()->create();
    $document = Document::factory()->withVersion()->create(['uploaded_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(DocumentLibrary::class, ['project' => $document->project])
        ->call('openNewVersion', $document->id)
        ->set('file', UploadedFile::fake()->create('contrato-v2.pdf', 500, 'application/pdf'))
        ->set('notes', 'Cláusulas actualizadas')
        ->call('saveVersion')
        ->assertHasNoErrors();

    expect($document->refresh()->versions)->toHaveCount(2)
        ->and($document->latestVersion->version)->toBe(2)
        ->and($document->latestVersion->notes)->toBe('Cláusulas actualizadas');
});

it('impide a los desarrolladores cargar archivos', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);

    Livewire::actingAs($developer)
        ->test(DocumentLibrary::class, ['project' => $project])
        ->call('openUpload')
        ->assertForbidden();
});

it('permite al miembro descargar la última versión', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);

    $document = Document::factory()->create(['project_id' => $project->id]);
    $path = UploadedFile::fake()->create('manual.pdf', 100)->store('documents');
    $document->versions()->create([
        'version' => 1,
        'file_path' => $path,
        'file_name' => 'manual.pdf',
        'file_size' => 1000,
        'mime_type' => 'application/pdf',
        'uploaded_by' => $document->uploaded_by,
    ]);

    $this->actingAs($developer)
        ->get(route('documents.download', $document))
        ->assertOk()
        ->assertDownload('manual.pdf');
});

it('impide descargar documentos de proyectos ajenos', function () {
    $outsider = User::factory()->create();
    $document = Document::factory()->withVersion()->create();

    $this->actingAs($outsider)
        ->get(route('documents.download', $document))
        ->assertForbidden();
});
