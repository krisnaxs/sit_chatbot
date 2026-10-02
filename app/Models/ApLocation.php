<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApLocation extends Model
{
    protected $fillable = [
        'bssid',
        'ssid',
        'location_id',
        'latitude',
        'longitude',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    // ============================================================
    // RELASI
    // ============================================================
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // ============================================================
    // HELPER
    // ============================================================
    public static function findByBssid(string $bssid): ?self
    {
        return self::where('bssid', strtoupper($bssid))
            ->where('is_active', true)
            ->with('location')
            ->first();
    }

    /**
     * Cek apakah AP ini punya koordinat.
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude && $this->longitude;
    }
}
