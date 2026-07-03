<?php

declare(strict_types=1);

namespace App\Domains\Comments\Models;

use App\Domains\Activities\Models\Activity;
use App\Domains\Comments\Enums\CommentStatus;
use App\Domains\Evidence\Models\Evidence;
use App\Domains\Projects\Models\Project;
use App\Domains\Screenshots\Models\Screenshot;
use App\Domains\Stages\Models\Stage;
use App\Domains\Users\Models\User;
use App\Support\Auditing\Auditable;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use Auditable;

    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'author_id',
        'parent_id',
        'commentable_type',
        'commentable_id',
        'content',
        'status',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return BelongsTo<self, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<self, $this> */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    /** @return HasMany<CommentAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(CommentAttachment::class);
    }

    /** @return HasMany<Screenshot, $this> */
    public function screenshots(): HasMany
    {
        return $this->hasMany(Screenshot::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The project this comment ultimately belongs to, whatever it was made on.
     */
    public function resolveProject(): ?Project
    {
        $commentable = $this->commentable;

        return match (true) {
            $commentable instanceof Project => $commentable,
            $commentable instanceof Stage,
            $commentable instanceof Activity,
            $commentable instanceof Evidence => $commentable->project,
            default => null,
        };
    }

    /**
     * Top-level comments (conversation roots, not replies).
     *
     * @param  Builder<self>  $query
     */
    public function scopeRoots(Builder $query): void
    {
        $query->whereNull('parent_id');
    }
}
