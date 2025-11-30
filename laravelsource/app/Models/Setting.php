<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            // Auto-detect type and cast
            return self::autoCastValue($setting->value);
        });
    }

    /**
     * Set a setting value by key
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set(string $key, $value): bool
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (is_bool($value) ? ($value ? 'true' : 'false') : (string) $value),
            ]
        );

        // Clear cache
        Cache::forget("setting_{$key}");

        return $setting->wasRecentlyCreated || $setting->wasChanged();
    }

    /**
     * Check if maintenance mode is enabled
     *
     * @return bool
     */
    public static function isMaintenanceMode(): bool
    {
        return self::get('maintenance_mode', false) === true;
    }

    /**
     * Get maintenance ETA
     *
     * @return string
     */
    public static function getMaintenanceETA(): string
    {
        return self::get('maintenance_eta', '00:00');
    }

    /**
     * Get maintenance message
     *
     * @return string
     */
    public static function getMaintenanceMessage(): string
    {
        return self::get('maintenance_message', 'The system is currently under maintenance.');
    }

    /**
     * Auto-cast value to appropriate type
     *
     * @param string|null $value
     * @return mixed
     */
    private static function autoCastValue(?string $value)
    {
        if ($value === null) {
            return null;
        }

        // Try to detect boolean
        if (in_array(strtolower($value), ['true', 'false', '1', '0'])) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        // Try to detect JSON
        if (str_starts_with($value, '{') || str_starts_with($value, '[')) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Try to detect integer
        if (is_numeric($value) && !str_contains($value, '.')) {
            return (int) $value;
        }

        // Try to detect float
        if (is_numeric($value) && str_contains($value, '.')) {
            return (float) $value;
        }

        // Return as string
        return $value;
    }

    /**
     * Clear all settings cache
     *
     * @return void
     */
    public static function clearCache(): void
    {
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget("setting_{$setting->key}");
        }
    }
}
