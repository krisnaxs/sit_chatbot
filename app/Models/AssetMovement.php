<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'movable_type',
        'movable_id',
        'from_location_id',
        'to_location_id',
        'type',
        'reference_table',
        'reference_id',
        'moved_at',
        'moved_by',
        'notes',
    ];

    protected function casts(): array
    {
        return ['moved_at' => 'datetime'];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function movedBy()
    {
        return $this->belongsTo(User::class, 'moved_by');
    }

    public function movable()
    {
        return $this->morphTo();
    }
}
