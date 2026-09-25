<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'vendor_id',
        'type',
        'issue',
        'action',
        'technician',
        'cost',
        'start_date',
        'end_date',
        'status',
        'condition_before',
        'condition_after',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'preventive' => 'Preventif',
            'corrective' => 'Perbaikan',
            'upgrade' => 'Upgrade',
            default => '-',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Dibuka',
            'in_progress' => 'Proses',
            'done' => 'Selesai',
            'cancelled' => 'Batal',
            default => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'yellow',
            'in_progress' => 'blue',
            'done' => 'green',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }
}
