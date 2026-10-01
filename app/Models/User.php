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
        'nip',
        'username',
        'name',
        'email',
        'password',
        'phone',
        'photo_path',
        'department_id',
        'position',
        'location_id',
        'role',
        'is_active',
        'waktu_pensiun',
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
            'waktu_pensiun' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->username) && !empty($user->email)) {
                $user->username = static::generateUsername($user->email);
            }
        });

        static::updating(function (User $user) {
            if ($user->isDirty('email')) {
                $user->username = static::generateUsername($user->email, $user->id);
            }
        });
    }

    /**
     * Generate username unik dari email (bagian sebelum @).
     */
    public static function generateUsername(string $email, ?int $ignoreUserId = null): string
    {
        $base = Str::before($email, '@');
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

    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->username})";
    }

    /**
     * Cek apakah user sudah masuk masa pensiun.
     * True kalau waktu_pensiun sudah lewat (atau hari ini).
     */
    public function getIsPensiunAttribute(): bool
    {
        return $this->waktu_pensiun !== null
            && $this->waktu_pensiun->isPast();
    }

    /**
     * Cek apakah user masih aktif bekerja (belum pensiun).
     */
    public function getIsAktifKerjaAttribute(): bool
    {
        return !$this->is_pensiun;
    }

    /**
     * Label status pensiun (untuk badge di view).
     */
    public function getStatusPensiunLabelAttribute(): string
    {
        if ($this->waktu_pensiun === null) {
            return 'Aktif';
        }

        if ($this->is_pensiun) {
            return 'Pensiun';
        }
        return 'Akan Pensiun';
    }

    /**
     * Warna badge status pensiun.
     */
    public function getStatusPensiunColorAttribute(): string
    {
        if ($this->waktu_pensiun === null) {
            return 'green';
        }

        if ($this->is_pensiun) {
            return 'gray';
        }

        return 'yellow';
    }

    /**
     * Scope: user yang sudah pensiun.
     */
    public function scopePensiun($query)
    {
        return $query->whereNotNull('waktu_pensiun')
            ->where('waktu_pensiun', '<=', now());
    }

    /**
     * Scope: user yang belum pensiun (termasuk yang tidak punya tanggal).
     */
    public function scopeBelumPensiun($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('waktu_pensiun')
                ->orWhere('waktu_pensiun', '>', now());
        });
    }

    /**
     * Scope: user yang akan pensiun dalam N hari ke depan.
     */
    public function scopeAkanPensiun($query, int $hari = 30)
    {
        return $query->whereNotNull('waktu_pensiun')
            ->whereBetween('waktu_pensiun', [
                now(),
                now()->addDays($hari),
            ]);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function currentAssets()
    {
        return $this->hasMany(Asset::class, 'current_user_id');
    }

    public function assetAssignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function activeLoans()
    {
        return $this->hasMany(AssetLoan::class)->whereNull('returned_at');
    }

    public function assetLoans()
    {
        return $this->hasMany(AssetLoan::class);
    }

    public function consumableTransactions()
    {
        return $this->hasMany(ConsumableTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
