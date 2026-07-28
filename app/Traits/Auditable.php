<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            ActivityLog::log($model, 'create', class_basename($model) . ' dibuat', null, $model->toArray());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            if (!empty($changes)) {
                ActivityLog::log($model, 'update', class_basename($model) . ' diperbarui', $model->getOriginal(), $model->toArray());
            }
        });

        static::deleted(function ($model) {
            ActivityLog::log($model, 'delete', class_basename($model) . ' dihapus', $model->toArray(), null);
        });
    }
}
