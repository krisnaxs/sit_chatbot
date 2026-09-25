<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_code',
        'serial_number',
        'hostname',
        'brand',
        'model',
        'category_id',
        'specification',
        'os',
        'os_license',
        'ownership_type',
        'purchase_date',
        'purchase_price',
        'warranty_expire',
        'status',
        'condition_percent',
        'condition_notes',
        'current_user_id',
        'current_location_id',
        'photo_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'specification' => 'array',
            'purchase_date' => 'date',
            'warranty_expire' => 'date',
            'purchase_price' => 'decimal:2',
        ];
    }

    // ============ AUTO-GENERATE asset_code ============
    protected static function booted(): void
    {
        static::creating(function (Asset $asset) {
            if (empty($asset->asset_code)) {
                $asset->asset_code = static::generateAssetCode();
            }
        });
    }

    public static function generateAssetCode(): string
    {
        $year = now()->format('Y');
        $prefix = "AST-{$year}-";

        $last = static::withTrashed()
            ->where('asset_code', 'like', "{$prefix}%")
            ->orderByDesc('asset_code')
            ->value('asset_code');

        $nextNumber = $last
            ? ((int) Str::afterLast($last, '-')) + 1
            : 1;

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // ============ RELASI ============
    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function currentUser()
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    public function currentLocation()
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }

    /**
     * History assignment (dari terbaru).
     */
    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class)->orderByDesc('assigned_at');
    }

    /**
     * Assignment aktif (belum dikembalikan).
     */
    public function activeAssignment()
    {
        return $this->hasOne(AssetAssignment::class)->whereNull('returned_at');
    }

    public function loans()
    {
        return $this->hasMany(AssetLoan::class)->orderByDesc('loan_date');
    }

    public function activeLoan()
    {
        return $this->hasOne(AssetLoan::class)->whereNull('returned_at');
    }

    public function maintenances()
    {
        return $this->hasMany(AssetMaintenance::class)->orderByDesc('start_date');
    }

    public function ownership()
    {
        return $this->hasOne(AssetOwnership::class)->latestOfMany();
    }

    public function ownerships()
    {
        return $this->hasMany(AssetOwnership::class);
    }

    public function movements()
    {
        return $this->hasMany(AssetMovement::class)->orderByDesc('moved_at');
    }

    public function attachments()
    {
        return $this->morphMany(AssetAttachment::class, 'attachable');
    }

    public function consumableTransactions()
    {
        return $this->hasMany(ConsumableTransaction::class);
    }

    // ============ SCOPE ============
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOwned($query)
    {
        return $query->where('ownership_type', 'owned');
    }

    public function scopeLeased($query)
    {
        return $query->where('ownership_type', 'leased');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeInUse($query)
    {
        return $query->where('status', 'in_use');
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('serial_number', 'like', "%{$keyword}%")
                ->orWhere('asset_code', 'like', "%{$keyword}%")
                ->orWhere('hostname', 'like', "%{$keyword}%")
                ->orWhere('brand', 'like', "%{$keyword}%")
                ->orWhere('model', 'like', "%{$keyword}%");
        });
    }

    // ============ ACCESSOR ============
    public function getFullNameAttribute(): string
    {
        return trim("{$this->brand} {$this->model}");
    }

    public function getOwnershipLabelAttribute(): string
    {
        return match ($this->ownership_type) {
            'owned' => 'Hak Milik',
            'leased' => 'Sewa',
            default => '-',
        };
    }

    public function getOwnershipColorAttribute(): string
    {
        return match ($this->ownership_type) {
            'owned' => 'green',
            'leased' => 'orange',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available' => 'Tersedia',
            'in_use' => 'Dipakai',
            'loaned' => 'Dipinjam',
            'maintenance' => 'Perbaikan',
            'retired' => 'Pensiun',
            'lost' => 'Hilang',
            default => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'available' => 'green',
            'in_use' => 'blue',
            'loaned' => 'yellow',
            'maintenance' => 'orange',
            'retired' => 'gray',
            'lost' => 'red',
            default => 'gray',
        };
    }

    public function getConditionColorAttribute(): string
    {
        $c = $this->condition_percent ?? 0;
        return match (true) {
            $c >= 80 => 'green',
            $c >= 60 => 'yellow',
            $c >= 40 => 'orange',
            default => 'red',
        };
    }
}
