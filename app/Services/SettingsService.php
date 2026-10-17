<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    protected const CACHE_KEY = 'app_settings';
    protected const CACHE_TTL = 3600; // 1 jam


    public static function get(string $group, string $key, $default = null)
    {
        $all = static::all();
        return $all[$group][$key] ?? config("{$group}.{$key}") ?? $default;
    }


    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $rows = Setting::all();
            $result = [];
            foreach ($rows as $row) {
                $result[$row->group][$row->key] = $row->value;
            }
            return $result;
        });
    }


    public static function putMany(string $group, array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::put($group, $key, $value);
        }
        static::forget();
    }


    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
