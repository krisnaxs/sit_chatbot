<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'contact_person',
        'phone',
        'email',
        'address',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function assetOwnerships()
    {
        return $this->hasMany(AssetOwnership::class);
    }

    public function assetMaintenances()
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'sewa' => 'Sewa',
            'pembelian' => 'Pembelian',
            'both' => 'Sewa & Pembelian',
            default => '-',
        };
    }
}
