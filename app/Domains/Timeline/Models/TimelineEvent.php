<?php

declare(strict_types=1);

namespace App\Domains\Timeline\Models;

use App\Domains\Projects\Models\Project;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Users\Models\User;
use Database\Factories\TimelineEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TimelineEvent extends Model
{
    public const UPDATED_AT = null;

    /** @use HasFactory<TimelineEventFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'type',
        'subject_type',
        'subject_id',
        'description',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => TimelineEventType::class,
            'created_at' => 'datetime',
        ];
    }

    protected static function newFactory(): TimelineEventFactory
    {
        return TimelineEventFactory::new();
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
