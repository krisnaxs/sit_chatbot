<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetOwnership extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'vendor_id',
        'ownership_type',
        'contract_number',
        'contract_start',
        'contract_end',
        'monthly_cost',
        'purchase_price',
        'invoice_number',
        'pic_vendor',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'contract_start' => 'date',
            'contract_end' => 'date',
            'monthly_cost' => 'decimal:2',
            'purchase_price' => 'decimal:2',
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

    public function scopeLeased($query)
    {
        return $query->where('ownership_type', 'leased');
    }

    public function scopeOwned($query)
    {
        return $query->where('ownership_type', 'owned');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('ownership_type', 'leased')
            ->whereNotNull('contract_end')
            ->whereBetween('contract_end', [now(), now()->addDays($days)]);
    }

    public function scopeExpired($query)
    {
        return $query->where('ownership_type', 'leased')
            ->whereNotNull('contract_end')
            ->where('contract_end', '<', now());
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->ownership_type === 'leased'
            && $this->contract_end
            && $this->contract_end->isPast();
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->contract_end) {
            return null;
        }
        return now()->diffInDays($this->contract_end, false);
    }
}
