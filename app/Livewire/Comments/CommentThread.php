<?php

declare(strict_types=1);

namespace App\Livewire\Comments;

use App\Domains\Comments\DTOs\CommentData;
use App\Domains\Comments\Enums\CommentStatus;
use App\Domains\Comments\Models\Comment;
use App\Domains\Comments\Services\CommentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class CommentThread extends Component
{
    use WithFileUploads;

    public Model $commentable;

    public bool $compact = false;

    public string $content = '';

    public ?int $replyTo = null;

    public string $statusFilter = '';

    /** @var array<int, mixed> */
    public array $attachments = [];

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:5000'],
            'attachments' => ['array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    public function save(CommentService $service): void
    {
        $this->authorize('create', [Comment::class, $this->commentable]);

        $this->validate();

        $service->create(
            $this->commentable,
            new CommentData(content: $this->content, parentId: $this->replyTo),
            auth()->user(),
            $this->attachments,
        );

        $this->reset(['content', 'replyTo', 'attachments']);
        $this->dispatch('toast', message: 'Observación agregada.');
    }

    public function startReply(int $commentId): void
    {
        $this->replyTo = $commentId;
    }

    public function cancelReply(): void
    {
        $this->replyTo = null;
    }

    public function changeStatus(int $commentId, string $status, CommentService $service): void
    {
        $comment = Comment::query()->findOrFail($commentId);

        $this->authorize('changeStatus', $comment);

        $service->changeStatus($comment, CommentStatus::from($status));

        $this->dispatch('toast', message: 'Estado de la observación actualizado.');
    }

    public function deleteComment(int $commentId, CommentService $service): void
    {
        $comment = Comment::query()->findOrFail($commentId);

        $this->authorize('delete', $comment);

        $service->delete($comment);

        $this->dispatch('toast', message: 'Observación eliminada.');
    }

    public function render(): View
    {
        $comments = $this->commentable->comments()
            ->roots()
            ->with(['author', 'attachments', 'replies.author', 'replies.attachments'])
            ->when(CommentStatus::tryFrom($this->statusFilter), fn ($query, $status) => $query->where('status', $status->value))
            ->latest()
            ->get();

        return view('livewire.comments.comment-thread', [
            'comments' => $comments,
            'statuses' => CommentStatus::options(),
        ]);
    }
}
