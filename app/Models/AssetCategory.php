<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_consumable',
        'is_agent_monitored',   // ← BARU
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_consumable' => 'boolean',
            'is_agent_monitored' => 'boolean',   // ← BARU
            'is_active' => 'boolean',
        ];
    }

    // ============================================================
    // RELASI
    // ============================================================

    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    public function consumables()
    {
        return $this->hasMany(Consumable::class, 'category_id');
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeConsumable($query)
    {
        return $query->where('is_consumable', true);
    }

    public function scopeNonConsumable($query)
    {
        return $query->where('is_consumable', false);
    }

    /**
     * Scope: kategori yang dimonitor agent (laptop, PC, server).
     */
    public function scopeAgentMonitored($query)
    {
        return $query->where('is_agent_monitored', true);
    }
}
