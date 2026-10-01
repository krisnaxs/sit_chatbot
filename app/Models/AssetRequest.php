<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetRequest extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Constants — Hindari Typo
    |--------------------------------------------------------------------------
    */
    const TYPE_ASSIGNMENT = 'assignment';
    const TYPE_LOAN = 'loan';
    const TYPE_CONSUMABLE = 'consumable';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    public static array $types = [
        self::TYPE_ASSIGNMENT => 'Serah Terima Aset',
        self::TYPE_LOAN => 'Peminjaman Aset',
        self::TYPE_CONSUMABLE => 'Permintaan Konsumable',
    ];

    public static array $statuses = [
        self::STATUS_PENDING => 'Menunggu',
        self::STATUS_APPROVED => 'Disetujui',
        self::STATUS_REJECTED => 'Ditolak',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'request_number',
        'type',
        'user_id',
        'asset_id',
        'consumable_id',
        'quantity',
        'location_id',
        'department_id',
        'purpose',
        'needed_date',
        'due_date',
        'hostname',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'admin_notes',
        'assignment_id',
        'loan_id',
        'transaction_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'needed_date' => 'date',
        'due_date' => 'date',
        'quantity' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function consumable(): BelongsTo
    {
        return $this->belongsTo(Consumable::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AssetAssignment::class, 'assignment_id');
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(AssetLoan::class, 'loan_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(ConsumableTransaction::class, 'transaction_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopePending($q)
    {
        return $q->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($q)
    {
        return $q->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($q)
    {
        return $q->where('status', self::STATUS_REJECTED);
    }

    public function scopeCancelled($q)
    {
        return $q->where('status', self::STATUS_CANCELLED);
    }

    public function scopeForUser($q, $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function scopeOfType($q, $type)
    {
        return $q->where('type', $type);
    }

    /**
     * Urutkan: pending dulu, lalu approved, rejected, cancelled.
     * Dalam masing-masing grup, urut dari yang terbaru.
     */
    public function scopeOrderByPriority($q)
    {
        return $q->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected', 'cancelled')")
            ->orderByDesc('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers — Status Check
    |--------------------------------------------------------------------------
    */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Cek apakah bisa dibatalkan (hanya pending).
     */
    public function isCancellable(): bool
    {
        return $this->isPending();
    }

    /*
    |--------------------------------------------------------------------------
    | Request Number Generator
    |--------------------------------------------------------------------------
    | Format: REQ-YYYYMMDD-0001
    */
    public static function generateNumber(): string
    {
        $date = now()->format('Ymd');
        $last = static::whereDate('created_at', today())
            ->where('request_number', 'like', "REQ-{$date}-%")
            ->orderByDesc('id')
            ->first();

        $seq = $last ? ((int) substr($last->request_number, -4)) + 1 : 1;

        return sprintf('REQ-%s-%04d', $date, $seq);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors — Label & Warna
    |--------------------------------------------------------------------------
    */
    public function getTypeLabelAttribute(): string
    {
        return self::$types[$this->type] ?? $this->type;
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_ASSIGNMENT => 'indigo',
            self::TYPE_LOAN => 'amber',
            self::TYPE_CONSUMABLE => 'green',
            default => 'gray',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_ASSIGNMENT => '💻',
            self::TYPE_LOAN => '📅',
            self::TYPE_CONSUMABLE => '📦',
            default => '📄',
        };
    }
    public function getStatusLabelAttribute(): string
    {
        return self::$statuses[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_CANCELLED => 'gray',
            default => 'gray',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors — Item Display
    |--------------------------------------------------------------------------
    */

    /**
     * Nama item yang diajukan (aset / konsumable).
     * Contoh: "Lenovo ThinkPad T14" atau "Tinta HP 802 (2 pcs)"
     */
    public function getItemNameAttribute(): string
    {
        if ($this->asset) {
            return "{$this->asset->brand} {$this->asset->model}";
        }
        if ($this->consumable) {
            return "{$this->consumable->name} ({$this->quantity} {$this->consumable->unit})";
        }
        return '—';
    }

    /**
     * Detail item lengkap (dengan SN kalau ada).
     */
    public function getItemDetailAttribute(): string
    {
        if ($this->asset) {
            return "{$this->asset->brand} {$this->asset->model} [{$this->asset->serial_number}]";
        }
        if ($this->consumable) {
            return "{$this->consumable->name} × {$this->quantity}";
        }
        return '—';
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors — Eksekusi
    |--------------------------------------------------------------------------
    */

    /**
     * Cek apakah pengajuan ini sudah dieksekusi (auto-assign/loan/trx).
     */
    public function getIsExecutedAttribute(): bool
    {
        return match ($this->type) {
            self::TYPE_ASSIGNMENT => !is_null($this->assignment_id),
            self::TYPE_LOAN => !is_null($this->loan_id),
            self::TYPE_CONSUMABLE => !is_null($this->transaction_id),
            default => false,
        };
    }

    /**
     * Waktu diproses (approved_at kalau ada).
     */
    public function getProcessedAtAttribute()
    {
        return $this->approved_at;
    }
}
