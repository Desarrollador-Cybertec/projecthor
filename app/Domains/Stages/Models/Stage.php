<?php

declare(strict_types=1);

namespace App\Domains\Stages\Models;

use App\Domains\Activities\Enums\ActivityStatus;
use App\Domains\Activities\Models\Activity;
use App\Domains\Comments\Models\Comment;
use App\Domains\Projects\Models\Project;
use App\Domains\Stages\Enums\StageStatus;
use App\Support\Auditing\Auditable;
use Database\Factories\StageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stage extends Model
{
    use Auditable;

    /** @use HasFactory<StageFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'objective',
        'status',
        'progress',
        'starts_on',
        'estimated_end_on',
        'ended_on',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'status' => StageStatus::class,
            'progress' => 'integer',
            'starts_on' => 'date',
            'estimated_end_on' => 'date',
            'ended_on' => 'date',
            'position' => 'integer',
        ];
    }

    protected static function newFactory(): StageFactory
    {
        return StageFactory::new();
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<Activity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->orderBy('position');
    }

    /** @return MorphMany<Comment, $this> */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function isCompleted(): bool
    {
        return $this->status === StageStatus::Completed;
    }

    /**
     * Recalculate progress from the stage's activities. Stages without
     * activities keep their manually set progress.
     */
    public function recalculateProgress(): void
    {
        $total = $this->activities()->count();

        if ($total === 0) {
            return;
        }

        $finished = $this->activities()
            ->where('status', ActivityStatus::Finished->value)
            ->count();

        $this->update(['progress' => (int) round($finished / $total * 100)]);
    }
}
