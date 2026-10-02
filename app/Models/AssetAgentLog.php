<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetAgentLog extends Model
{
    protected $fillable = [
        'asset_id',
        'ip',
        'mac',
        'wifi_ssid',
        'wifi_bssid',
        'hostname',
        'logged_user',
        'uptime_hours',
        'cpu_temp',
        'reported_at',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'cpu_temp' => 'decimal:1',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
