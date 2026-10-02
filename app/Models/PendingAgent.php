<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingAgent extends Model
{
    protected $fillable = [
        'hostname',
        'serial_number',
        'mac_address',
        'ip',
        'wifi_ssid',
        'wifi_bssid',
        'logged_user',
        'attempt_count',
        'attempted_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_reason',
        'asset_id',
    ];

    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function scopeRejected($query)
    {
        return $query->whereNotNull('rejected_at');
    }
    public function scopePending($query)
    {
        return $query->whereNull('approved_at')
            ->whereNull('rejected_at');
    }


    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }
}
