<?php

declare(strict_types=1);

namespace App\Domains\Comments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CommentAttachment extends Model
{
    protected $fillable = [
        'comment_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    /** @return BelongsTo<Comment, $this> */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function fileUrl(): string
    {
        return Storage::url($this->file_path);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }
}
