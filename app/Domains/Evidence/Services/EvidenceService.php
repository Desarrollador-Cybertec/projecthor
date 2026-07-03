<?php

declare(strict_types=1);

namespace App\Domains\Evidence\Services;

use App\Domains\Activities\Models\Activity;
use App\Domains\Evidence\DTOs\EvidenceData;
use App\Domains\Evidence\Enums\EvidenceType;
use App\Domains\Evidence\Events\EvidenceUploaded;
use App\Domains\Evidence\Models\Evidence;
use App\Domains\Users\Models\User;
use App\Support\Images\ImageThumbnailer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EvidenceService
{
    public function __construct(
        private ImageThumbnailer $thumbnailer,
    ) {}

    /**
     * Store one uploaded file as evidence of the given activity.
     */
    public function storeFile(
        Activity $activity,
        UploadedFile $file,
        EvidenceData $data,
        User $author,
    ): Evidence {
        $path = $file->store("projects/{$activity->project_id}/evidences", ['disk' => config('filesystems.default')]);
        $type = $data->type ?? EvidenceType::fromFile(
            $file->getMimeType() ?? '',
            $file->getClientOriginalExtension(),
        );

        $evidence = Evidence::query()->create([
            'project_id' => $activity->project_id,
            'activity_id' => $activity->id,
            'author_id' => $author->id,
            'name' => $data->name,
            'description' => $data->description,
            'type' => $type,
            'version' => $data->version,
            'file_path' => $path,
            'thumbnail_path' => $type === EvidenceType::Image ? $this->thumbnailer->make($path) : null,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        EvidenceUploaded::dispatch($evidence, $author);

        return $evidence;
    }

    /**
     * Store a link-based evidence (enlace, Figma, producción).
     */
    public function storeLink(Activity $activity, EvidenceData $data, User $author): Evidence
    {
        $evidence = Evidence::query()->create([
            'project_id' => $activity->project_id,
            'activity_id' => $activity->id,
            'author_id' => $author->id,
            'name' => $data->name,
            'description' => $data->description,
            'type' => $data->type ?? EvidenceType::Link,
            'version' => $data->version,
            'url' => $data->url,
        ]);

        EvidenceUploaded::dispatch($evidence, $author);

        return $evidence;
    }

    public function update(Evidence $evidence, EvidenceData $data): Evidence
    {
        $evidence->update([
            'name' => $data->name,
            'description' => $data->description,
            'version' => $data->version,
            'url' => $evidence->type->isLinkBased() ? ($data->url ?? $evidence->url) : $evidence->url,
        ]);

        return $evidence->refresh();
    }

    public function delete(Evidence $evidence): void
    {
        foreach ([$evidence->file_path, $evidence->thumbnail_path] as $path) {
            if ($path) {
                Storage::delete($path);
            }
        }

        $evidence->delete();
    }
}
