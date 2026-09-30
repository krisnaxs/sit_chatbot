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

    // ═══════════════════════════════════════════
    //  RELASI
    // ═══════════════════════════════════════════

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

    /**
     * 🆕 Relasi ke AssetRequest — inverse dari `$req->loan_id`.
     *
     * Catatan:
     * - Pakai `hasOne` karena foreign key (`loan_id`) ada di tabel `asset_requests`.
     * - Satu loan hanya boleh terkait dengan satu request.
     * - Loan yang dibuat manual (via form) tidak punya request → null.
     */
    public function assetRequest()
    {
        return $this->hasOne(AssetRequest::class, 'loan_id', 'id');
    }

    // ═══════════════════════════════════════════
    //  ACCESSOR — Info Pengajuan
    // ═══════════════════════════════════════════

    /**
     * 🆕 Nomor pengajuan (null kalau loan dibuat manual, bukan dari approval).
     */
    public function getRequestNumberAttribute(): ?string
    {
        return $this->assetRequest?->request_number;
    }

    /**
     * 🆕 Apakah loan ini berasal dari pengajuan (bukan dibuat manual).
     */
    public function getIsFromRequestAttribute(): bool
    {
        return $this->assetRequest !== null;
    }

    /**
     * 🆕 URL ke detail pengajuan (null kalau dibuat manual).
     */
    public function getRequestUrlAttribute(): ?string
    {
        if (!$this->assetRequest) {
            return null;
        }

        // Cek apakah route `requests.show` ada (fallback aman)
        if (!\Route::has('requests.show')) {
            return null;
        }

        return route('requests.show', $this->assetRequest);
    }

    // ═══════════════════════════════════════════
    //  SCOPE — Existing
    // ═══════════════════════════════════════════

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

    // ═══════════════════════════════════════════
    //  SCOPE — 🆕 Filter by Origin
    // ═══════════════════════════════════════════

    /**
     * 🆕 Hanya loan yang berasal dari pengajuan (approval).
     */
    public function scopeFromRequest($query)
    {
        return $query->whereHas('assetRequest');
    }

    /**
     * 🆕 Hanya loan yang dibuat manual (bukan dari pengajuan).
     */
    public function scopeManual($query)
    {
        return $query->whereDoesntHave('assetRequest');
    }

    // ═══════════════════════════════════════════
    //  ACCESSOR — Status
    // ═══════════════════════════════════════════

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
