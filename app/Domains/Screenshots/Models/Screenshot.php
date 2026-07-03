<?php

declare(strict_types=1);

namespace App\Domains\Screenshots\Models;

use App\Domains\Activities\Models\Activity;
use App\Domains\Comments\Models\Comment;
use App\Domains\Projects\Models\Project;
use App\Domains\Stages\Models\Stage;
use App\Domains\Users\Models\User;
use App\Support\Auditing\Auditable;
use Database\Factories\ScreenshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
        'stage_id',
        'activity_id',
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

    /** @return BelongsTo<Stage, $this> */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    /** @return BelongsTo<Activity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return MorphMany<Comment, $this> */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
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
