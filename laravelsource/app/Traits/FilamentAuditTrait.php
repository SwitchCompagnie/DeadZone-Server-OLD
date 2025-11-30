<?php

namespace App\Traits;

use App\Services\AdminAuditService;

trait FilamentAuditTrait
{
    protected array $auditOriginalValues = [];

    protected function beforeFill(): void
    {
        if (method_exists(parent::class, 'beforeFill')) {
            parent::beforeFill();
        }

        if ($this->record->exists) {
            $this->auditOriginalValues = $this->record->getAttributes();
        }
    }

    protected function beforeSave(): void
    {
        if (method_exists(parent::class, 'beforeSave')) {
            parent::beforeSave();
        }
    }

    protected function afterCreate(): void
    {
        if (method_exists(parent::class, 'afterCreate')) {
            parent::afterCreate();
        }

        AdminAuditService::logCreate(
            $this->record,
            $this->getAuditResourceName(),
            $this->record->getAttributes()
        );
    }

    protected function afterSave(): void
    {
        if (method_exists(parent::class, 'afterSave')) {
            parent::afterSave();
        }

        if (! $this->record->wasRecentlyCreated && ! empty($this->auditOriginalValues)) {
            $currentAttributes = $this->record->getAttributes();
            $oldValues = [];
            $newValues = [];

            foreach ($currentAttributes as $key => $newValue) {
                if (array_key_exists($key, $this->auditOriginalValues) && $this->auditOriginalValues[$key] !== $newValue) {
                    $oldValues[$key] = $this->auditOriginalValues[$key];
                    $newValues[$key] = $newValue;
                }
            }

            if (! empty($oldValues)) {
                AdminAuditService::logUpdate(
                    $this->record,
                    $this->getAuditResourceName(),
                    $oldValues,
                    $newValues
                );
            }
        }
    }

    protected function afterDelete(): void
    {
        if (method_exists(parent::class, 'afterDelete')) {
            parent::afterDelete();
        }

        AdminAuditService::logDelete(
            $this->record,
            $this->getAuditResourceName()
        );
    }

    protected function getAuditResourceName(): string
    {
        $resource = static::getResource();

        if (method_exists($resource, 'getModelLabel')) {
            return $resource::getModelLabel();
        }

        return class_basename($this->record);
    }
}
