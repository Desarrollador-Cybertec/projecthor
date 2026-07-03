<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Comments\Models\CommentAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommentAttachmentDownloadController extends Controller
{
    public function __invoke(CommentAttachment $attachment): StreamedResponse
    {
        abort_unless(auth()->user()->can('view', $attachment->comment), 403);

        return Storage::download($attachment->file_path, $attachment->file_name);
    }
}
