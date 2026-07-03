<?php

declare(strict_types=1);

namespace App\Domains\Comments\Services;

use App\Domains\Comments\DTOs\CommentData;
use App\Domains\Comments\Enums\CommentStatus;
use App\Domains\Comments\Events\CommentAdded;
use App\Domains\Comments\Models\Comment;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CommentService
{
    /**
     * @param  list<UploadedFile>  $attachments
     */
    public function create(
        Model $commentable,
        CommentData $data,
        User $author,
        array $attachments = [],
    ): Comment {
        $comment = DB::transaction(function () use ($commentable, $data, $author, $attachments): Comment {
            $comment = Comment::query()->create([
                'author_id' => $author->id,
                'parent_id' => $data->parentId,
                'commentable_type' => $commentable->getMorphClass(),
                'commentable_id' => $commentable->getKey(),
                'content' => $data->content,
                'status' => CommentStatus::Open,
            ]);

            foreach ($attachments as $attachment) {
                $comment->attachments()->create([
                    'file_path' => $attachment->store('comments/attachments', ['disk' => config('filesystems.default')]),
                    'file_name' => $attachment->getClientOriginalName(),
                    'file_size' => $attachment->getSize() ?: 0,
                    'mime_type' => $attachment->getMimeType(),
                ]);
            }

            return $comment;
        });

        CommentAdded::dispatch($comment, $author);

        return $comment;
    }

    public function changeStatus(Comment $comment, CommentStatus $status): Comment
    {
        $comment->update([
            'status' => $status,
            'resolved_at' => $status === CommentStatus::Resolved ? now() : null,
        ]);

        return $comment->refresh();
    }

    public function delete(Comment $comment): void
    {
        foreach ($comment->attachments as $attachment) {
            Storage::delete($attachment->file_path);
        }

        $comment->delete();
    }
}
