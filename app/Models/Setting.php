<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Cache key prefix
     */
    const CACHE_PREFIX = 'settings_';

    /**
     * Cache duration in seconds (1 hour)
     */
    const CACHE_DURATION = 3600;

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    public function scopeByKey($query, $key)
    {
        return $query->where('key', $key);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get the value attribute with type casting
     */
    public function getValueAttribute($value)
    {
        return match ($this->type) {
            'integer', 'int' => (int) $value,
            'float', 'double' => (float) $value,
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    // ==========================================
    // STATIC HELPER METHODS
    // ==========================================

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @param string|null $type
     * @return Setting
     */
    public static function set(string $key, $value, ?string $group = 'general', ?string $type = null)
    {
        // Determine the type if not provided
        if ($type === null) {
            $type = self::determineType($value);
        }

        // Convert value to string for storage
        $storedValue = self::prepareValueForStorage($value, $type);

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'group' => $group,
                'type' => $type,
            ]
        );

        // Clear cache for this key
        Cache::forget(self::CACHE_PREFIX . $key);

        return $setting;
    }

    /**
     * Check if a setting exists
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Remove a setting
     *
     * @param string $key
     * @return bool
     */
    public static function remove(string $key): bool
    {
        Cache::forget(self::CACHE_PREFIX . $key);
        return static::where('key', $key)->delete() > 0;
    }

    /**
     * Get all settings by group
     *
     * @param string $group
     * @return \Illuminate\Support\Collection
     */
    public static function getByGroup(string $group)
    {
        return static::byGroup($group)->get()->pluck('value', 'key');
    }

    /**
     * Get all settings as key-value array
     *
     * @return array
     */
    public static function getAllAsArray(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
        }
    }

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    /**
     * Determine the type of a value
     *
     * @param mixed $value
     * @return string
     */
    private static function determineType($value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'json',
            default => 'string',
        };
    }

    /**
     * Prepare value for database storage
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    private static function prepareValueForStorage($value, string $type): string
    {
        return match ($type) {
            'boolean', 'bool' => $value ? '1' : '0',
            'json', 'array' => json_encode($value),
            default => (string) $value,
        };
    }
}
