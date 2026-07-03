<?php

declare(strict_types=1);

namespace App\Domains\Screenshots\Services;

use App\Domains\Comments\Models\Comment;
use App\Domains\Screenshots\Events\ScreenshotUploaded;
use App\Domains\Screenshots\Models\Screenshot;
use App\Domains\Users\Models\User;
use App\Support\Images\ImageThumbnailer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ScreenshotService
{
    public function __construct(
        private ImageThumbnailer $thumbnailer,
    ) {}

    public function store(
        Comment $comment,
        UploadedFile $image,
        ?string $caption,
        User $author,
    ): Screenshot {
        $project = $comment->resolveProject();

        $path = $image->store(
            'projects/'.($project?->id ?? 'sin-proyecto').'/screenshots',
            ['disk' => config('filesystems.default')],
        );

        $screenshot = Screenshot::query()->create([
            'project_id' => $project?->id,
            'comment_id' => $comment->id,
            'author_id' => $author->id,
            'image_path' => $path,
            'thumbnail_path' => $this->thumbnailer->make($path),
            'description' => $caption,
            'taken_at' => now()->toDateString(),
        ]);

        ScreenshotUploaded::dispatch($screenshot, $author);

        return $screenshot;
    }

    public function delete(Screenshot $screenshot): void
    {
        foreach ([$screenshot->image_path, $screenshot->thumbnail_path] as $path) {
            if ($path) {
                Storage::delete($path);
            }
        }

        $screenshot->delete();
    }
}
