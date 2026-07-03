<?php

declare(strict_types=1);

namespace App\Domains\Evidence\Models;

use App\Domains\Activities\Models\Activity;
use App\Domains\Comments\Models\Comment;
use App\Domains\Evidence\Enums\EvidenceType;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use App\Support\Auditing\Auditable;
use Database\Factories\EvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Evidence extends Model
{
    use Auditable;

    /** @use HasFactory<EvidenceFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'evidences';

    protected $fillable = [
        'project_id',
        'activity_id',
        'author_id',
        'name',
        'description',
        'type',
        'version',
        'file_path',
        'thumbnail_path',
        'file_name',
        'file_size',
        'mime_type',
        'url',
    ];

    protected function casts(): array
    {
        return [
            'type' => EvidenceType::class,
            'file_size' => 'integer',
        ];
    }

    protected static function newFactory(): EvidenceFactory
    {
        return EvidenceFactory::new();
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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

    public function fileUrl(): ?string
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    public function thumbnailUrl(): ?string
    {
        return $this->thumbnail_path ? Storage::url($this->thumbnail_path) : null;
    }
}
