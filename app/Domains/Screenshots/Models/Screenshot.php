<?php

declare(strict_types=1);

namespace App\Domains\Screenshots\Models;

use App\Domains\Comments\Models\Comment;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use App\Support\Auditing\Auditable;
use Database\Factories\ScreenshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Screenshot extends Model
{
    use Auditable;

    /** @use HasFactory<ScreenshotFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'comment_id',
        'author_id',
        'view_name',
        'module',
        'resolution',
        'platform',
        'image_path',
        'thumbnail_path',
        'description',
        'notes',
        'version',
        'taken_at',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'date',
        ];
    }

    protected static function newFactory(): ScreenshotFactory
    {
        return ScreenshotFactory::new();
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<Comment, $this> */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function imageUrl(): string
    {
        return Storage::url($this->image_path);
    }

    public function thumbnailUrl(): ?string
    {
        return $this->thumbnail_path ? Storage::url($this->thumbnail_path) : null;
    }
}
