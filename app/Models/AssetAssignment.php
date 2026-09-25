<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'hostname',
        'user_id',
        'location_id',
        'department_id',
        'assigned_at',
        'returned_at',
        'assigned_by',
        'received_by',
        'condition_on_assign',
        'condition_on_return',
        'handover_doc_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('returned_at');
    }

    public function scopeReturned($query)
    {
        return $query->whereNotNull('returned_at');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->returned_at === null;
    }

    public function getDurationDaysAttribute(): ?int
    {
        if (!$this->assigned_at) {
            return null;
        }
        $end = $this->returned_at ?? now();
        return $this->assigned_at->diffInDays($end);
    }
}
