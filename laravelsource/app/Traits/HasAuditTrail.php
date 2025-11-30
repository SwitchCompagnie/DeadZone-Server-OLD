<?php

namespace App\Traits;

use App\Services\AdminAuditService;
use Illuminate\Database\Eloquent\Model;

trait HasAuditTrail
{
    protected static array $auditOriginalCache = [];

    protected static function bootHasAuditTrail(): void
    {
        static::updating(function (Model $model) {
            $cacheKey = get_class($model).':'.$model->getKey();
            static::$auditOriginalCache[$cacheKey] = $model->getOriginal();
        });

        static::updated(function (Model $model) {
            $cacheKey = get_class($model).':'.$model->getKey();
            $originalValues = static::$auditOriginalCache[$cacheKey] ?? [];
            unset(static::$auditOriginalCache[$cacheKey]);

            $changes = $model->getChanges();

            if (empty($changes)) {
                return;
            }

            $oldValues = [];
            $newValues = [];

            foreach ($changes as $key => $newValue) {
                $oldValues[$key] = $originalValues[$key] ?? null;
                $newValues[$key] = $newValue;
            }

            AdminAuditService::logUpdate(
                $model,
                static::getAuditResourceName(),
                $oldValues,
                $newValues
            );
        });

        static::created(function (Model $model) {
            AdminAuditService::logCreate(
                $model,
                static::getAuditResourceName(),
                $model->getAttributes()
            );
        });

        static::deleted(function (Model $model) {
            AdminAuditService::logDelete(
                $model,
                static::getAuditResourceName()
            );
        });

        static::restored(function (Model $model) {
            AdminAuditService::logRestore(
                $model,
                static::getAuditResourceName()
            );
        });
    }

    protected static function getAuditResourceName(): string
    {
        if (method_exists(static::class, 'getCustomAuditName')) {
            return static::getCustomAuditName();
        }

        return static::getModelLabel();
    }
}
