<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumableTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'consumable_id',
        'user_id',
        'type',
        'quantity',
        'transaction_date',
        'requested_by',
        'approved_by',
        'location_id',
        'asset_id',
        'purpose',
        'notes',
    ];

    protected function casts(): array
    {
        return ['transaction_date' => 'datetime'];
    }

    public function consumable()
    {
        return $this->belongsTo(Consumable::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function scopeIn($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeOut($query)
    {
        return $query->where('type', 'out');
    }

    public function scopeReturn($query)
    {
        return $query->where('type', 'return');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in' => 'Masuk',
            'out' => 'Keluar',
            'return' => 'Kembali',
            default => '-',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'in' => 'green',
            'out' => 'red',
            'return' => 'blue',
            default => 'gray',
        };
    }
}
