<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consumable extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'brand',
        'model',
        'unit',
        'stock_total',
        'stock_available',
        'stock_minimum',
        'last_price',
        'notes',
    ];

    protected function casts(): array
    {
        return ['last_price' => 'decimal:2'];
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function transactions()
    {
        return $this->hasMany(ConsumableTransaction::class)->orderByDesc('transaction_date');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_available', '<=', 'stock_minimum');
    }

    public function scopeAvailable($query)
    {
        return $query->where('stock_available', '>', 0);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_available <= $this->stock_minimum;
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->brand} {$this->model}") ?: $this->name;
    }

    public function getStockPercentageAttribute(): float
    {
        if ($this->stock_total <= 0) {
            return 0;
        }
        return round(($this->stock_available / $this->stock_total) * 100, 1);
    }
}
