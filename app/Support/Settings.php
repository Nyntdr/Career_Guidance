<?php

namespace App\Support;

use App\Models\Setting;

class Settings
{
    protected static array $cache = [];

    protected static bool $loaded = false;

    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();

        $definition = config("settings.definitions.{$key}") ?? [];

        return self::$cache[$key] ?? $default ?? ($definition['default'] ?? null);
    }

    /**
     * Effective values for every defined setting (DB override or default).
     */
    public static function all(): array
    {
        self::load();

        $result = [];

        foreach ((array) config('settings.definitions') as $key => $definition) {
            $result[$key] = self::$cache[$key] ?? ($definition['default'] ?? null);
        }

        return $result;
    }

    /**
     * Persist overrides. Passing null / '' for a key removes its override
     * so the configured default takes over again.
     */
    public static function save(array $values): void
    {
        foreach ((array) config('settings.definitions') as $key => $definition) {
            if (!array_key_exists($key, $values)) {
                continue;
            }

            $value = $values[$key];

            if ($value === null || $value === '') {
                Setting::where('key', $key)->delete();
                continue;
            }

            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        self::flush();
    }

    public static function flush(): void
    {
        self::$cache = [];
        self::$loaded = false;
    }

    protected static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        foreach (Setting::query()->orderBy('key')->get() as $setting) {
            self::$cache[$setting->key] = $setting->value;
        }

        self::$loaded = true;
    }
}