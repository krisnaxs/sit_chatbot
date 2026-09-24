<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Atribut yang bisa diisi massal.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',        // 🆕 admin | support | user
        'is_active',   // 🆕 true | false
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
        ];
    }

    // ============================================================
    // ROLE HELPER METHODS
    // ============================================================

    /**
     * Cek apakah user adalah admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user adalah support.
     */
    public function isSupport(): bool
    {
        return $this->role === 'support';
    }

    /**
     * Cek apakah user punya role tertentu.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Cek apakah user punya salah satu dari roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Cek apakah user aktif.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    // ============================================================
    // ATTRIBUTE ACCESSORS
    // ============================================================

    /**
     * Label role untuk tampilan.
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'support' => 'Support',
            'user' => 'User',
            default => 'Tidak diketahui',
        };
    }

    /**
     * Warna badge role (untuk Tailwind).
     */
    public function getRoleColorAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'violet',
            'support' => 'blue',
            'user' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Inisial nama untuk avatar.
     */
    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name ?? 'U', 0, 1));
    }
}
