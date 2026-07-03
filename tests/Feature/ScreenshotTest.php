<?php

declare(strict_types=1);

use App\Domains\Projects\Models\Project;
use App\Domains\Screenshots\Models\Screenshot;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Users\Models\User;
use App\Livewire\Comments\CommentThread;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

it('agrega capturas a una observación con miniatura y registra el evento', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);

    Livewire::actingAs($developer)
        ->test(CommentThread::class, ['commentable' => $project])
        ->set('content', 'Observación con capturas')
        ->set('captures', [UploadedFile::fake()->image('captura.png', 1200, 800)])
        ->call('save')
        ->assertHasNoErrors();

    $comment = $project->comments()->first();
    $screenshot = Screenshot::query()->where('comment_id', $comment?->id)->first();

    expect($comment)->not->toBeNull()
        ->and($screenshot)->not->toBeNull()
        ->and($screenshot->comment_id)->toBe($comment->id)
        ->and($screenshot->project_id)->toBe($project->id)
        ->and($screenshot->thumbnail_path)->not->toBeNull()
        ->and($project->timelineEvents()->where('type', TimelineEventType::ScreenshotAdded->value)->exists())->toBeTrue();

    Storage::disk('public')->assertExists($screenshot->image_path);
    Storage::disk('public')->assertExists($screenshot->thumbnail_path);
});

it('rechaza capturas que no son imágenes', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);

    Livewire::actingAs($developer)
        ->test(CommentThread::class, ['commentable' => $project])
        ->set('content', 'Observación')
        ->set('captures', [UploadedFile::fake()->create('archivo.pdf', 100, 'application/pdf')])
        ->call('save')
        ->assertHasErrors('captures.*');
});

it('impide agregar capturas a quien no es miembro del proyecto', function () {
    $outsider = User::factory()->create();
    $project = Project::factory()->create();

    Livewire::actingAs($outsider)
        ->test(CommentThread::class, ['commentable' => $project])
        ->set('content', 'Intento')
        ->set('captures', [UploadedFile::fake()->image('captura.png')])
        ->call('save')
        ->assertForbidden();
});
