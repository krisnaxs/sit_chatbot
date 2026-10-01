<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value'];

    /**
     * Ambil value setting, fallback ke default.
     */
    public static function get(string $group, string $key, $default = null)
    {
        $row = static::where('group', $group)->where('key', $key)->first();
        return $row?->value ?? $default;
    }

    /**
     * Simpan/update setting.
     */
    public static function put(string $group, string $key, $value): void
    {
        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => is_array($value) ? json_encode($value) : $value]
        );
    }

    /**
     * Ambil semua setting di grup tertentu sebagai array assoc.
     */
    public static function group(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Hapus seluruh setting di grup.
     */
    public static function clearGroup(string $group): void
    {
        static::where('group', $group)->delete();
    }
}
