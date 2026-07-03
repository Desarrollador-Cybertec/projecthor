<?php

declare(strict_types=1);

namespace App\Domains\Activities\Models;

use App\Domains\Activities\Enums\ActivityStatus;
use App\Domains\Comments\Models\Comment;
use App\Domains\Evidence\Models\Evidence;
use App\Domains\Projects\Enums\Priority;
use App\Domains\Projects\Models\Project;
use App\Domains\Stages\Models\Stage;
use App\Domains\Users\Models\User;
use App\Support\Auditing\Auditable;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use Auditable;

    /** @use HasFactory<ActivityFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'stage_id',
        'name',
        'description',
        'responsible_id',
        'priority',
        'status',
        'position',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => Priority::class,
            'status' => ActivityStatus::class,
            'position' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ActivityFactory
    {
        return ActivityFactory::new();
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

    /** @return BelongsTo<User, $this> */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    /** @return HasMany<Evidence, $this> */
    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    /** @return MorphMany<Comment, $this> */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function isFinished(): bool
    {
        return $this->status === ActivityStatus::Finished;
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $term = trim($term);

        if ($term === '') {
            return;
        }

        $like = '%'.mb_strtolower($term).'%';

        $query->where(function (Builder $query) use ($like): void {
            $query->whereRaw('LOWER(name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(description) LIKE ?', [$like]);
        });
    }
}
