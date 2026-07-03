<?php

declare(strict_types=1);

namespace App\Livewire\Comments;

use App\Domains\Comments\DTOs\CommentData;
use App\Domains\Comments\Enums\CommentStatus;
use App\Domains\Comments\Models\Comment;
use App\Domains\Comments\Services\CommentService;
use App\Domains\Screenshots\Models\Screenshot;
use App\Domains\Screenshots\Services\ScreenshotService;
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

    /** @var array<int, mixed> */
    public array $captures = [];

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:5000'],
            'attachments' => ['array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
            'captures' => ['array', 'max:5'],
            'captures.*' => ['image', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'captures' => 'capturas',
            'captures.*' => 'captura',
        ];
    }

    public function save(CommentService $service, ScreenshotService $screenshots): void
    {
        $this->authorize('create', [Comment::class, $this->commentable]);

        $this->validate();

        $comment = $service->create(
            $this->commentable,
            new CommentData(content: $this->content, parentId: $this->replyTo),
            auth()->user(),
            $this->attachments,
        );

        foreach ($this->captures as $capture) {
            $screenshots->store($comment, $capture, null, auth()->user());
        }

        $this->reset(['content', 'replyTo', 'attachments', 'captures']);
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

    public function deleteScreenshot(int $screenshotId, ScreenshotService $service): void
    {
        $screenshot = Screenshot::query()->findOrFail($screenshotId);

        $this->authorize('delete', $screenshot);

        $service->delete($screenshot);

        $this->dispatch('toast', message: 'Captura eliminada.');
    }

    public function render(): View
    {
        $comments = $this->commentable->comments()
            ->roots()
            ->with([
                'author',
                'attachments',
                'screenshots',
                'replies' => fn ($query) => $query
                    ->with(['author', 'attachments', 'screenshots'])
                    ->withCount('screenshots'),
            ])
            ->withCount('screenshots')
            ->when(CommentStatus::tryFrom($this->statusFilter), fn ($query, $status) => $query->where('status', $status->value))
            ->latest()
            ->get();

        return view('livewire.comments.comment-thread', [
            'comments' => $comments,
            'statuses' => CommentStatus::options(),
        ]);
    }
}
