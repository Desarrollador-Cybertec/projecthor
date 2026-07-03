<?php

declare(strict_types=1);

namespace App\Domains\Files\Models;

use App\Domains\Files\Enums\DocumentCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use App\Support\Auditing\Auditable;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use Auditable;

    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'uploaded_by',
        'category',
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'category' => DocumentCategory::class,
        ];
    }

    protected static function newFactory(): DocumentFactory
    {
        return DocumentFactory::new();
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** @return HasMany<DocumentVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('version');
    }

    /** @return HasOne<DocumentVersion, $this> */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(DocumentVersion::class)->ofMany('version', 'max');
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
