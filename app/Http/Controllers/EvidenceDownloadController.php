<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Evidence\Models\Evidence;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvidenceDownloadController extends Controller
{
    public function __invoke(Evidence $evidence): StreamedResponse
    {
        abort_unless(auth()->user()->can('view', $evidence), 403);
        abort_if($evidence->file_path === null, 404);

        return Storage::download($evidence->file_path, $evidence->file_name ?? basename($evidence->file_path));
    }
}
