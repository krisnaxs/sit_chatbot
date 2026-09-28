<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatContext extends Model
{
    protected $fillable = [
        'session_id',
        'type',
        'data',
        'expires_at',
        'label',
        'hit_count',
    ];

    protected $casts = [
        'data' => 'array',
        'expires_at' => 'datetime',
        'hit_count' => 'integer',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
}
