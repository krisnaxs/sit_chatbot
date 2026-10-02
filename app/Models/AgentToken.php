<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AgentToken extends Model
{
    protected $fillable = [
        'token',
        'name',
        'asset_id',
        'is_active',
        'device_serial',
        'device_hostname',
        'last_used_at',
        'last_ip',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public static function generate(string $name, ?int $assetId = null): self
    {
        return self::create([
            'token' => Str::random(64),
            'name' => $name,
            'asset_id' => $assetId,
            'is_active' => true,
        ]);
    }

    public function revoke(): void
    {
        $this->update(['is_active' => false]);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function regenerate(): void
    {
        $this->update([
            'token' => Str::random(64),
            'is_active' => true,
        ]);
    }
}
