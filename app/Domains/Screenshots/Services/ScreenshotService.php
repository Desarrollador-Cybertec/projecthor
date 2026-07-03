<?php

declare(strict_types=1);

namespace App\Domains\Screenshots\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Screenshots\DTOs\ScreenshotData;
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
        Project $project,
        UploadedFile $image,
        ScreenshotData $data,
        User $author,
    ): Screenshot {
        $path = $image->store("projects/{$project->id}/screenshots", ['disk' => config('filesystems.default')]);

        $screenshot = Screenshot::query()->create([
            'project_id' => $project->id,
            'stage_id' => $data->stageId,
            'activity_id' => $data->activityId,
            'author_id' => $author->id,
            'view_name' => $data->viewName,
            'module' => $data->module,
            'resolution' => $data->resolution,
            'platform' => $data->platform,
            'image_path' => $path,
            'thumbnail_path' => $this->thumbnailer->make($path),
            'description' => $data->description,
            'notes' => $data->notes,
            'version' => $data->version,
            'taken_at' => $data->takenAt ?? now()->toDateString(),
        ]);

        ScreenshotUploaded::dispatch($screenshot, $author);

        return $screenshot;
    }

    public function update(Screenshot $screenshot, ScreenshotData $data): Screenshot
    {
        $screenshot->update([
            'stage_id' => $data->stageId,
            'activity_id' => $data->activityId,
            'view_name' => $data->viewName,
            'module' => $data->module,
            'resolution' => $data->resolution,
            'platform' => $data->platform,
            'description' => $data->description,
            'notes' => $data->notes,
            'version' => $data->version,
            'taken_at' => $data->takenAt,
        ]);

        return $screenshot->refresh();
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
