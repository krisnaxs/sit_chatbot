<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'user_id',
        'loan_date',
        'due_date',
        'returned_at',
        'purpose',
        'approved_by',
        'status',
        'condition_on_loan',
        'condition_on_return',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'loan_date' => 'datetime',
            'due_date' => 'datetime',
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

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('returned_at')
            ->whereIn('status', ['approved', 'borrowed']);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNull('returned_at')
            ->where('due_date', '<', now());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->returned_at === null
            && $this->due_date
            && $this->due_date->isPast();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'borrowed' => 'Dipinjam',
            'returned' => 'Dikembalikan',
            'overdue' => 'Terlambat',
            default => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'approved' => 'blue',
            'borrowed' => 'indigo',
            'returned' => 'green',
            'overdue' => 'red',
            default => 'gray',
        };
    }
}
