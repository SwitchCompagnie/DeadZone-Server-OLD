<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AdminAuditService
{
    public static function log(
        string $action,
        ?Model $resource = null,
        ?string $resourceType = null,
        ?string $resourceName = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?array $metadata = null
    ): AdminAuditLog {
        $user = Auth::user();

        if ($resource) {
            $resourceType = get_class($resource);
            $resourceId = $resource->getKey();
            $resourceTitle = self::getResourceTitle($resource);
        } else {
            $resourceId = null;
            $resourceTitle = null;
        }

        return AdminAuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_name' => $resourceName,
            'resource_id' => $resourceId,
            'resource_title' => $resourceTitle,
            'description' => $description ?? self::generateDescription($action, $resourceName, $resourceTitle),
            'old_values' => $oldValues ? self::sanitizeValues($oldValues) : null,
            'new_values' => $newValues ? self::sanitizeValues($newValues) : null,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
        ]);
    }

    public static function logView(?Model $resource = null, ?string $resourceName = null): AdminAuditLog
    {
        return self::log('view', $resource, null, $resourceName);
    }

    public static function logCreate(Model $resource, ?string $resourceName = null, ?array $values = null): AdminAuditLog
    {
        return self::log('create', $resource, null, $resourceName, null, $values ?? $resource->getAttributes());
    }

    public static function logUpdate(Model $resource, ?string $resourceName = null, ?array $oldValues = null, ?array $newValues = null): AdminAuditLog
    {
        return self::log('update', $resource, null, $resourceName, $oldValues, $newValues);
    }

    public static function logDelete(Model $resource, ?string $resourceName = null): AdminAuditLog
    {
        return self::log('delete', $resource, null, $resourceName, $resource->getAttributes());
    }

    public static function logRestore(Model $resource, ?string $resourceName = null): AdminAuditLog
    {
        return self::log('restore', $resource, null, $resourceName);
    }

    private static function getResourceTitle(Model $resource): ?string
    {
        $titleAttributes = ['name', 'title', 'label', 'username', 'email', 'slug'];

        foreach ($titleAttributes as $attribute) {
            if (isset($resource->$attribute)) {
                return (string) $resource->$attribute;
            }
        }

        return "#{$resource->getKey()}";
    }

    private static function generateDescription(string $action, ?string $resourceName, ?string $resourceTitle): string
    {
        $actionLabels = [
            'view' => 'viewed',
            'create' => 'created',
            'update' => 'updated',
            'delete' => 'deleted',
            'restore' => 'restored',
        ];

        $actionLabel = $actionLabels[$action] ?? $action;
        $resource = $resourceName ?? 'resource';
        $title = $resourceTitle ? " \"{$resourceTitle}\"" : '';

        return ucfirst("{$actionLabel} {$resource}{$title}");
    }

    private static function sanitizeValues(array $values): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'remember_token', 'token', 'api_token', 'secret'];

        foreach ($sensitiveFields as $field) {
            if (isset($values[$field])) {
                $values[$field] = '[HIDDEN]';
            }
        }

        return $values;
    }

    public static function getChanges(array $old, array $new): array
    {
        $changes = [];

        foreach ($new as $key => $value) {
            if (! array_key_exists($key, $old) || $old[$key] !== $value) {
                $changes[$key] = [
                    'old' => $old[$key] ?? null,
                    'new' => $value,
                ];
            }
        }

        return $changes;
    }

    public static function getUserStats(?int $userId = null, ?string $period = 'week'): array
    {
        $query = AdminAuditLog::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($period) {
            $date = match ($period) {
                'day' => now()->subDay(),
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
                default => now()->subWeek(),
            };

            $query->where('created_at', '>=', $date);
        }

        return [
            'total' => $query->count(),
            'by_action' => $query->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action')
                ->toArray(),
            'by_resource' => $query->selectRaw('resource_name, COUNT(*) as count')
                ->whereNotNull('resource_name')
                ->groupBy('resource_name')
                ->pluck('count', 'resource_name')
                ->toArray(),
        ];
    }
}
