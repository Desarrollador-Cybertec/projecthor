<?php

declare(strict_types=1);

namespace App\Domains\Files\Models;

use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DocumentVersion extends Model
{
    protected $fillable = [
        'document_id',
        'version',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'uploaded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'file_size' => 'integer',
        ];
    }

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function fileUrl(): string
    {
        return Storage::url($this->file_path);
    }

    public function isPreviewable(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/')
            || $this->mime_type === 'application/pdf';
    }
}
