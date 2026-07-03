<?php

declare(strict_types=1);

namespace App\Domains\Files\Services;

use App\Domains\Files\DTOs\DocumentData;
use App\Domains\Files\Events\DocumentUploaded;
use App\Domains\Files\Models\Document;
use App\Domains\Files\Models\DocumentVersion;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function create(
        Project $project,
        DocumentData $data,
        UploadedFile $file,
        User $uploader,
    ): Document {
        $document = DB::transaction(function () use ($project, $data, $file, $uploader): Document {
            $document = $project->documents()->create([
                'uploaded_by' => $uploader->id,
                'category' => $data->category,
                'name' => $data->name,
                'description' => $data->description,
            ]);

            $this->storeVersion($document, $file, $uploader, 1, $data->notes);

            return $document;
        });

        DocumentUploaded::dispatch($document, $uploader);

        return $document;
    }

    public function addVersion(
        Document $document,
        UploadedFile $file,
        User $uploader,
        ?string $notes = null,
    ): DocumentVersion {
        $next = ((int) $document->versions()->max('version')) + 1;

        $version = $this->storeVersion($document, $file, $uploader, $next, $notes);

        DocumentUploaded::dispatch($document, $uploader, isNewVersion: true);

        return $version;
    }

    public function update(Document $document, DocumentData $data): Document
    {
        $document->update([
            'category' => $data->category,
            'name' => $data->name,
            'description' => $data->description,
        ]);

        return $document->refresh();
    }

    public function delete(Document $document): void
    {
        foreach ($document->versions as $version) {
            Storage::delete($version->file_path);
        }

        $document->delete();
    }

    private function storeVersion(
        Document $document,
        UploadedFile $file,
        User $uploader,
        int $version,
        ?string $notes,
    ): DocumentVersion {
        return $document->versions()->create([
            'version' => $version,
            'file_path' => $file->store("projects/{$document->project_id}/documents", ['disk' => config('filesystems.default')]),
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize() ?: 0,
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => $uploader->id,
            'notes' => $notes,
        ]);
    }
}
