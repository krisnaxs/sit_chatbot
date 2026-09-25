<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Atribut yang bisa diisi massal.
     */
    protected $fillable = [
        // Identitas
        'nip',
        'username',
        'name',
        'email',
        'password',
        'phone',
        'photo_path',

        // Organisasi
        'department_id',
        'position',
        'location_id',

        // Role & status
        'role',
        'is_active',
    ];

    /**
     * Atribut yang disembunyikan saat serialisasi.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting atribut.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    // ============================================================
    // AUTO-GENERATE USERNAME DARI EMAIL
    // ============================================================

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->username) && !empty($user->email)) {
                $user->username = static::generateUsername($user->email);
            }
        });

        static::updating(function (User $user) {
            // Regenerate username kalau email berubah
            if ($user->isDirty('email')) {
                $user->username = static::generateUsername($user->email, $user->id);
            }
        });
    }

    /**
     * Generate username unik dari email (bagian sebelum @).
     * Kalau sudah dipakai, tambah angka: budi, budi1, budi2, dst.
     */
    public static function generateUsername(string $email, ?int $ignoreUserId = null): string
    {
        $base = Str::before($email, '@');

        // Sanitasi: hanya huruf, angka, titik, underscore, dash
        $base = preg_replace('/[^a-zA-Z0-9._-]/', '', $base);
        $base = strtolower($base);
        $base = trim($base, '._-');

        if (empty($base)) {
            $base = 'user';
        }

        $username = $base;
        $counter = 1;

        while (static::usernameExists($username, $ignoreUserId)) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Cek apakah username sudah dipakai (termasuk yang soft deleted).
     */
    protected static function usernameExists(string $username, ?int $ignoreUserId = null): bool
    {
        $query = static::withTrashed()->where('username', $username);

        if ($ignoreUserId) {
            $query->where('id', '!=', $ignoreUserId);
        }

        return $query->exists();
    }

    // ============================================================
    // ROLE HELPER METHODS
    // ============================================================

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSupport(): bool
    {
        return $this->role === 'support';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    // ============================================================
    // ATTRIBUTE ACCESSORS
    // ============================================================

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'support' => 'Support',
            'user' => 'User',
            default => 'Tidak diketahui',
        };
    }

    public function getRoleColorAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'violet',
            'support' => 'blue',
            'user' => 'gray',
            default => 'gray',
        };
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name ?? 'U', 0, 1));
    }

    /**
     * Nama lengkap + role (untuk dropdown/select).
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->username})";
    }

    // ============================================================
    // RELASI — ORGANISASI
    // ============================================================

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // ============================================================
    // RELASI — ASSET (SIAM)
    // ============================================================

    /**
     * Aset yang SEDANG dipegang user ini.
     */
    public function currentAssets()
    {
        return $this->hasMany(Asset::class, 'current_user_id');
    }

    /**
     * History semua assignment aset ke user ini.
     */
    public function assetAssignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    /**
     * Peminjaman aktif (belum dikembalikan).
     */
    public function activeLoans()
    {
        return $this->hasMany(AssetLoan::class)->whereNull('returned_at');
    }

    /**
     * Semua peminjaman (history).
     */
    public function assetLoans()
    {
        return $this->hasMany(AssetLoan::class);
    }

    /**
     * Transaksi konsumable user ini.
     */
    public function consumableTransactions()
    {
        return $this->hasMany(ConsumableTransaction::class);
    }

    // ============================================================
    // SCOPE
    // ============================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
