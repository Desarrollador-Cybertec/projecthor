<?php

declare(strict_types=1);

use App\Domains\Comments\Enums\CommentStatus;
use App\Domains\Comments\Models\Comment;
use App\Domains\Projects\Models\Project;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Users\Models\User;
use App\Domains\Users\Notifications\ProjectEventNotification;
use App\Livewire\Comments\CommentThread;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

it('crea observaciones sobre un proyecto con adjuntos', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);

    Livewire::actingAs($developer)
        ->test(CommentThread::class, ['commentable' => $project])
        ->set('content', 'El logo del cliente debe actualizarse.')
        ->set('attachments', [UploadedFile::fake()->image('logo.png')])
        ->call('save')
        ->assertHasNoErrors();

    $comment = $project->comments()->first();

    expect($comment)->not->toBeNull()
        ->and($comment->status)->toBe(CommentStatus::Open)
        ->and($comment->attachments)->toHaveCount(1)
        ->and($project->timelineEvents()->where('type', TimelineEventType::CommentAdded->value)->exists())->toBeTrue();
});

it('notifica al equipo al crear una observación', function () {
    Notification::fake();

    $developer = User::factory()->create();
    $responsible = User::factory()->admin()->create();
    $project = Project::factory()->create(['responsible_id' => $responsible->id]);
    $project->members()->attach($developer);

    Livewire::actingAs($developer)
        ->test(CommentThread::class, ['commentable' => $project])
        ->set('content', 'Observación de prueba')
        ->call('save');

    Notification::assertSentTo($responsible, ProjectEventNotification::class);
    Notification::assertNotSentTo($developer, ProjectEventNotification::class);
});

it('permite responder observaciones', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);
    $root = Comment::factory()->on($project)->create();

    Livewire::actingAs($developer)
        ->test(CommentThread::class, ['commentable' => $project])
        ->call('startReply', $root->id)
        ->set('content', 'Respuesta a la observación')
        ->call('save')
        ->assertHasNoErrors();

    expect($root->replies()->count())->toBe(1);
});

it('cambia el estado de una observación', function () {
    $developer = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach($developer);
    $comment = Comment::factory()->on($project)->create();

    Livewire::actingAs($developer)
        ->test(CommentThread::class, ['commentable' => $project])
        ->call('changeStatus', $comment->id, 'resolved')
        ->assertHasNoErrors();

    expect($comment->refresh())
        ->status->toBe(CommentStatus::Resolved)
        ->resolved_at->not->toBeNull();
});

it('impide comentar a quien no pertenece al proyecto', function () {
    $outsider = User::factory()->create();
    $project = Project::factory()->create();

    Livewire::actingAs($outsider)
        ->test(CommentThread::class, ['commentable' => $project])
        ->set('content', 'No debería poder')
        ->call('save')
        ->assertForbidden();
});

it('solo el autor puede eliminar su observación', function () {
    $author = User::factory()->create();
    $other = User::factory()->create();
    $project = Project::factory()->create();
    $project->members()->attach([$author->id, $other->id]);
    $comment = Comment::factory()->on($project)->create(['author_id' => $author->id]);

    Livewire::actingAs($other)
        ->test(CommentThread::class, ['commentable' => $project])
        ->call('deleteComment', $comment->id)
        ->assertForbidden();

    Livewire::actingAs($author)
        ->test(CommentThread::class, ['commentable' => $project])
        ->call('deleteComment', $comment->id)
        ->assertHasNoErrors();

    $this->assertSoftDeleted('comments', ['id' => $comment->id]);
});
