<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Geri alınabilir silme (çöp kutusu).
 *
 * - delete() kaydı çöp kutusuna taşır; $cascadeDeletes ilişkilerindeki kayıtlar da birlikte taşınır.
 * - restore() kaydı ve onunla birlikte (aynı anda ya da sonra) silinmiş alt kayıtları geri getirir;
 *   önceden ayrıca silinmiş alt kayıtlar çöp kutusunda kalır.
 * - Çöp kutusunda Trash::RETENTION_DAYS günden uzun kalan kayıtlar "model:prune" ile kalıcı silinir
 *   (alt kayıtlar veritabanı cascade ile gider).
 *
 * Modelde: protected array $cascadeDeletes = ['ilişki', ...];
 */
trait Trashable
{
    use Prunable, SoftDeletes;

    /** restore sırasında alt kayıtları eşlemek için silinme anı (veritabanı sütunu değil) */
    protected $trashedAtBeforeRestore = null;

    public static function bootTrashable(): void
    {
        static::deleted(function ($model) {
            if ($model->isForceDeleting()) {
                return;
            }
            foreach ($model->cascadeDeleteRelations() as $relation) {
                $model->{$relation}()->get()->each->delete();
            }
        });

        static::restoring(function ($model) {
            $model->trashedAtBeforeRestore = $model->getOriginal($model->getDeletedAtColumn());
        });

        static::restored(function ($model) {
            if (! $from = $model->trashedAtBeforeRestore) {
                return;
            }
            foreach ($model->cascadeDeleteRelations() as $relation) {
                $related = $model->{$relation}()->getRelated();
                $model->{$relation}()->onlyTrashed()
                    ->where($related->qualifyColumn($related->getDeletedAtColumn()), '>=', $from)
                    ->get()->each->restore();
            }
            $model->trashedAtBeforeRestore = null;
        });
    }

    /** @return list<string> */
    public function cascadeDeleteRelations(): array
    {
        return property_exists($this, 'cascadeDeletes') ? $this->cascadeDeletes : [];
    }

    /** Çöp kutusunda saklama süresi dolanlar (model:prune). */
    public function prunable(): Builder
    {
        return static::onlyTrashed()->where($this->getDeletedAtColumn(), '<=', now()->subDays(\App\Support\Trash::RETENTION_DAYS));
    }
}
