<?php

declare(strict_types=1);

namespace App\Domains\Projects\Models;

use App\Domains\Activities\Models\Activity;
use App\Domains\Comments\Models\Comment;
use App\Domains\Evidence\Models\Evidence;
use App\Domains\Files\Models\Document;
use App\Domains\Projects\Enums\Priority;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Screenshots\Models\Screenshot;
use App\Domains\Stages\Enums\StageStatus;
use App\Domains\Stages\Models\Stage;
use App\Domains\Timeline\Models\TimelineEvent;
use App\Domains\Users\Models\User;
use App\Support\Auditing\Auditable;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    use Auditable;

    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'client_name',
        'logo_path',
        'color',
        'description',
        'responsible_id',
        'start_date',
        'due_date',
        'finished_at',
        'priority',
        'status',
        'production_url',
        'staging_url',
        'documentation_url',
        'repository_url',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'finished_at' => 'datetime',
            'priority' => Priority::class,
            'status' => ProjectStatus::class,
        ];
    }

    protected static function newFactory(): ProjectFactory
    {
        return ProjectFactory::new();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<User, $this> */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /** @return HasMany<Stage, $this> */
    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class)->orderBy('position');
    }

    /** @return HasMany<Activity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /** @return HasMany<Evidence, $this> */
    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    /** @return HasMany<Screenshot, $this> */
    public function screenshots(): HasMany
    {
        return $this->hasMany(Screenshot::class);
    }

    /** @return HasMany<Document, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /** @return HasMany<TimelineEvent, $this> */
    public function timelineEvents(): HasMany
    {
        return $this->hasMany(TimelineEvent::class)->latest('created_at');
    }

    /** @return MorphMany<Comment, $this> */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Overall progress: the average progress of the project's stages.
     */
    protected function progress(): Attribute
    {
        return Attribute::get(function (): int {
            $stages = $this->relationLoaded('stages')
                ? $this->stages
                : $this->stages()->get(['id', 'progress']);

            if ($stages->isEmpty()) {
                return 0;
            }

            return (int) round((float) $stages->avg('progress'));
        });
    }

    public function currentStage(): ?Stage
    {
        $stages = $this->relationLoaded('stages') ? $this->stages : $this->stages()->get();

        return $stages->first(fn (Stage $stage): bool => $stage->status !== StageStatus::Completed)
            ?? $stages->last();
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::url($this->logo_path) : null;
    }

    public function isFinished(): bool
    {
        return $this->status === ProjectStatus::Completed;
    }

    /**
     * Everyone involved in the project: assigned members plus the responsible.
     *
     * @return Collection<int, User>
     */
    public function team(): Collection
    {
        return $this->members
            ->concat([$this->responsible])
            ->filter()
            ->unique('id')
            ->values();
    }

    public function isMember(User $user): bool
    {
        return $this->responsible_id === $user->id
            || $this->members->contains('id', $user->id);
    }

    /**
     * Projects the given user is allowed to see.
     *
     * @param  Builder<self>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $query->where(function (Builder $query) use ($user): void {
            $query->where('responsible_id', $user->id)
                ->orWhereHas('members', fn (Builder $members) => $members->whereKey($user->id));
        });
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
                ->orWhereRaw('LOWER(client_name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(description) LIKE ?', [$like]);
        });
    }
}
