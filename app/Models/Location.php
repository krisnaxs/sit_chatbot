<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'building',
        'floor',
        'room',
        'division',
        'full_name',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    // Auto-generate full_name
    protected static function booted(): void
    {
        static::saving(function (Location $location) {
            $parts = array_filter([
                $location->building,
                $location->floor,
                $location->room,
            ]);

            if (empty($location->full_name) && !empty($parts)) {
                $location->full_name = implode(' - ', $parts);
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function currentAssets()
    {
        return $this->hasMany(Asset::class, 'current_location_id');
    }

    public function assetAssignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function consumableTransactions()
    {
        return $this->hasMany(ConsumableTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?? trim("{$this->building} - {$this->room}", ' -');
    }
}
