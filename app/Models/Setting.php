<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value'];


    public static function get(string $group, string $key, $default = null)
    {
        $row = static::where('group', $group)->where('key', $key)->first();
        return $row?->value ?? $default;
    }


    public static function put(string $group, string $key, $value): void
    {
        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => is_array($value) ? json_encode($value) : $value]
        );
    }


    public static function group(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }


    public static function clearGroup(string $group): void
    {
        static::where('group', $group)->delete();
    }
}
