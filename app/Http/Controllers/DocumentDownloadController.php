<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Files\Models\Document;
use App\Domains\Files\Models\DocumentVersion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentDownloadController extends Controller
{
    /**
     * Download the latest version of the document.
     */
    public function latest(Document $document): StreamedResponse
    {
        abort_unless(auth()->user()->can('download', $document), 403);

        $version = $document->latestVersion;

        abort_if($version === null, 404);

        return Storage::download($version->file_path, $version->file_name);
    }

    /**
     * Download a specific version of the document.
     */
    public function version(Document $document, DocumentVersion $version): StreamedResponse
    {
        abort_unless(auth()->user()->can('download', $document), 403);
        abort_unless($version->document_id === $document->id, 404);

        return Storage::download($version->file_path, $version->file_name);
    }
}
