<?php

declare(strict_types=1);

namespace App\Support\Auditing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

/**
 * Records every create, update and delete on the model into the audits table.
 *
 * @mixin Model
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            /** @var Model&Auditable $model */
            $model->recordAudit('created', [], $model->auditableValues($model->getAttributes()));
        });

        static::updated(function (Model $model): void {
            /** @var Model&Auditable $model */
            $new = $model->auditableValues($model->getChanges());
            unset($new['updated_at']);

            if ($new === []) {
                return;
            }

            $old = array_intersect_key($model->auditableValues($model->getRawOriginal()), $new);
            $model->recordAudit('updated', $old, $new);
        });

        static::deleted(function (Model $model): void {
            /** @var Model&Auditable $model */
            $model->recordAudit('deleted', [], []);
        });
    }

    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable')->latest('created_at');
    }

    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     */
    protected function recordAudit(string $event, array $old, array $new): void
    {
        Audit::query()->create([
            'user_id' => Auth::id(),
            'auditable_type' => $this->getMorphClass(),
            'auditable_id' => $this->getKey(),
            'event' => $event,
            'old_values' => $old === [] ? null : $old,
            'new_values' => $new === [] ? null : $new,
            'created_at' => now(),
        ]);
    }

    /**
     * Strip hidden attributes (passwords, tokens) from audited values.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function auditableValues(array $values): array
    {
        return array_diff_key($values, array_flip($this->getHidden()));
    }
}
