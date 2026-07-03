<?php

declare(strict_types=1);

namespace App\Domains\Users\Models;

use App\Domains\Activities\Models\Activity;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Enums\UserRole;
use App\Support\Auditing\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Auditable;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'avatar_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? Storage::url($this->avatar_path) : null;
    }

    /** @return HasMany<Project, $this> */
    public function responsibleProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'responsible_id');
    }

    /** @return BelongsToMany<Project, $this> */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    /** @return HasMany<Activity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'responsible_id');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
