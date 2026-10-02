<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApLocation extends Model
{
    protected $fillable = [
        'bssid',
        'ssid',
        'location_id',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public static function findByBssid(string $bssid): ?self
    {
        return self::where('bssid', strtoupper($bssid))
            ->where('is_active', true)
            ->with('location')
            ->first();
    }
}
