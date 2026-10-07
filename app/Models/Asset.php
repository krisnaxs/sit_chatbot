<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PendingAgent;
use App\Models\AgentToken;
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

        // === Agent Tracking ===
        'last_seen_at',
        'last_ip',
        'last_lat',
        'last_lng',
        'location_source',
        'last_mac',
        'last_wifi_ssid',
        'last_wifi_bssid',
        'last_logged_user',
        'last_uptime_hours',
        'last_cpu_temp',
        'agent_version',
        'last_os',
        'agent_status',
    ];

    protected function casts(): array
    {
        return [
            'specification' => 'array',
            'purchase_date' => 'date',
            'warranty_expire' => 'date',
            'purchase_price' => 'decimal:2',

            // === Agent Tracking ===
            'last_seen_at' => 'datetime',
            'last_cpu_temp' => 'decimal:1',
            'last_lat' => 'decimal:7',
            'last_lng' => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Asset $asset) {
            if (empty($asset->asset_code)) {
                $asset->asset_code = static::generateAssetCode();
            }
        });

        // === TAMBAHKAN INI ===
        static::created(function (Asset $asset) {
            static::autoApprovePendingAgent($asset);
        });
    }

    /**
     * Auto-approve pending agent kalau serial number-nya cocok
     * dengan asset yang baru dibuat.
     */
    protected static function autoApprovePendingAgent(Asset $asset): void
    {
        // Skip kalau asset tidak punya serial number
        if (empty($asset->serial_number)) {
            return;
        }

        // Cari pending agent yang serial-nya cocok & belum di-approve/reject
        $pending = PendingAgent::pending()
            ->where('serial_number', $asset->serial_number)
            ->first();

        if (!$pending) {
            return;
        }

        // Buat token agent
        $token = AgentToken::create([
            'token' => Str::random(64),
            'name' => "{$asset->asset_code} Agent",
            'asset_id' => $asset->id,
            'is_active' => true,
            'device_serial' => $pending->serial_number,
            'device_hostname' => $pending->hostname,
        ]);

        // Update hostname asset kalau belum ada
        if (empty($asset->hostname)) {
            $asset->update(['hostname' => $pending->hostname]);
        }

        // Tandai pending sudah di-approve
        $pending->update([
            'approved_at' => now(),
            'approved_by' => auth()->id() ?? null,
            'asset_id' => $asset->id,
        ]);
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

    // ============================================================
    // RELASI EXISTING
    // ============================================================

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

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class)->orderByDesc('assigned_at');
    }

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

    // ============================================================
    // RELASI AGENT
    // ============================================================

    public function agentToken()
    {
        return $this->hasOne(AgentToken::class);
    }

    public function agentLogs()
    {
        return $this->hasMany(AssetAgentLog::class)->latest('reported_at');
    }

    public function latestAgentLog()
    {
        return $this->hasOne(AssetAgentLog::class)->latestOfMany('reported_at');
    }

    public function pendingAgent()
    {
        return $this->hasOne(PendingAgent::class);
    }

    // ============================================================
    // SCOPES EXISTING
    // ============================================================

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

    public function scopePerluDitarik($query, int $hari = 30)
    {
        return $query->whereNotNull('current_user_id')
            ->whereIn('status', ['in_use', 'loaned'])
            ->whereHas('currentUser', function ($q) use ($hari) {
                $q->whereNotNull('waktu_pensiun')
                    ->where('waktu_pensiun', '<=', now()->addDays($hari));
            });
    }

    public function scopeDipegangPensiunan($query)
    {
        return $query->whereNotNull('current_user_id')
            ->whereHas('currentUser', function ($q) {
                $q->whereNotNull('waktu_pensiun')
                    ->where('waktu_pensiun', '<=', now());
            });
    }

    public function scopeAkanDitarik($query, int $hari = 30)
    {
        return $query->whereNotNull('current_user_id')
            ->whereHas('currentUser', function ($q) use ($hari) {
                $q->whereNotNull('waktu_pensiun')
                    ->where('waktu_pensiun', '>', now())
                    ->where('waktu_pensiun', '<=', now()->addDays($hari));
            });
    }

    // ============================================================
    // SCOPES AGENT
    // ============================================================

    public function scopeAgentMonitored($query)
    {
        return $query->whereHas('category', function ($q) {
            $q->where('is_agent_monitored', true);
        });
    }

    public function scopeAgentOnline($query)
    {
        return $query->whereNotNull('last_seen_at')
            ->where('last_seen_at', '>=', now()->subMinutes(10));
    }

    public function scopeAgentIdle($query)
    {
        return $query->whereNotNull('last_seen_at')
            ->whereBetween('last_seen_at', [
                now()->subMinutes(60),
                now()->subMinutes(10),
            ]);
    }

    public function scopeAgentOffline($query)
    {
        return $query->whereNotNull('last_seen_at')
            ->where('last_seen_at', '<', now()->subMinutes(60));
    }

    public function scopeAgentNeverReported($query)
    {
        return $query->whereNull('last_seen_at');
    }

    /**
     * Scope: hanya aset yang punya koordinat.
     */
    public function scopeHasCoordinates($query)
    {
        return $query->whereNotNull('last_lat')
            ->whereNotNull('last_lng');
    }

    // ============================================================
    // ACCESSORS EXISTING
    // ============================================================

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

    public function getOwnershipLabelWithVendorAttribute(): string
    {
        $base = match ($this->ownership_type) {
            'owned' => 'HAK MILIK',
            'leased' => 'SEWA',
            default => '-',
        };

        $vendorName = $this->ownership?->vendor?->name;

        return $vendorName
            ? "{$base} \"{$vendorName}\""
            : $base;
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

    public function getPublicUrlAttribute(): string
    {
        return route('assets.public', ['serial' => rawurlencode($this->serial_number)]);
    }

    public function getPerluDitarikAttribute(): bool
    {
        if (!$this->currentUser || !$this->currentUser->waktu_pensiun) {
            return false;
        }

        return $this->currentUser->waktu_pensiun->lte(now()->addDays(30));
    }

    public function getDipegangPensiunanAttribute(): bool
    {
        if (!$this->currentUser || !$this->currentUser->waktu_pensiun) {
            return false;
        }

        return $this->currentUser->waktu_pensiun->isPast();
    }

    public function getSisaHariPensiunAttribute(): ?int
    {
        if (!$this->currentUser || !$this->currentUser->waktu_pensiun) {
            return null;
        }

        return (int) now()->diffInDays($this->currentUser->waktu_pensiun, false);
    }

    public function getLabelPengingatPensiunAttribute(): ?string
    {
        $sisa = $this->sisa_hari_pensiun;

        if ($sisa === null) {
            return null;
        }

        return match (true) {
            $sisa < 0 => 'Pensiun ' . abs($sisa) . ' hari lalu',
            $sisa === 0 => 'Pensiun hari ini',
            $sisa <= 7 => "Pensiun {$sisa} hari lagi",
            $sisa <= 30 => "Pensiun {$sisa} hari lagi",
            default => null,
        };
    }

    public function getWarnaPengingatPensiunAttribute(): ?string
    {
        $sisa = $this->sisa_hari_pensiun;

        if ($sisa === null) {
            return null;
        }

        return match (true) {
            $sisa < 0 => 'red',
            $sisa === 0 => 'red',
            $sisa <= 7 => 'orange',
            $sisa <= 30 => 'yellow',
            default => null,
        };
    }

    // ============================================================
    // ACCESSORS AGENT
    // ============================================================

    public function isOnline(): bool
    {
        return $this->last_seen_at
            && $this->last_seen_at->diffInMinutes(now()) < 10;
    }

    public function isIdle(): bool
    {
        if (!$this->last_seen_at)
            return false;
        $min = $this->last_seen_at->diffInMinutes(now());
        return $min >= 10 && $min < 60;
    }

    public function isOffline(): bool
    {
        return $this->last_seen_at
            && $this->last_seen_at->diffInMinutes(now()) >= 60;
    }

    public function hasAgent(): bool
    {
        return !is_null($this->last_seen_at);
    }

    /**
     * Cek apakah aset punya koordinat GPS.
     */
    public function hasCoordinates(): bool
    {
        return !is_null($this->last_lat) && !is_null($this->last_lng);
    }

    public function getAgentStatusLabelAttribute(): string
    {
        if (!$this->hasAgent())
            return 'Belum Install';
        if ($this->isOnline())
            return 'Online';
        if ($this->isIdle())
            return 'Idle';
        return 'Offline';
    }

    public function getAgentStatusColorAttribute(): string
    {
        if (!$this->hasAgent())
            return 'gray';
        if ($this->isOnline())
            return 'green';
        if ($this->isIdle())
            return 'yellow';
        return 'red';
    }
}
