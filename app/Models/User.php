<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'password',
        'nama_lengkap',
        'email',
        'jabatan',
        'bagian',
        'role_id',
        'is_active',
        'must_change_pwd',
        'last_login',
    ];

    protected function casts(): array
    {
        return [
            'is_active'      => 'boolean',
            'must_change_pwd' => 'boolean',
            'last_login'     => 'datetime',
        ];
    }

    protected $hidden = ['password', 'remember_token'];

    // ── Relasi ──────────────────────────────────────────────────────────────

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function tiketDiajukan()
    {
        return $this->hasMany(Ticket::class, 'pemohon_id');
    }

    public function tiketDitugaskan()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    // ── Role Helpers ─────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return in_array($this->role?->nama, ['admin', 'superadmin']);
    }

    public function isPimpinan(): bool
    {
        return $this->role?->nama === 'pimpinan';
    }

    public function isOperator(): bool
    {
        return $this->role?->nama === 'operator';
    }

    public function isUser(): bool
    {
        return $this->role?->nama === 'user';
    }

    public function isKabag(): bool
    {
        return in_array($this->role?->nama, ['kabag_umum', 'kabag_aset', 'kabag_pengadaan']);
    }

    public function isStaf(): bool
    {
        return in_array($this->role?->nama, ['staf_umum', 'staf_aset', 'staf_pengadaan']);
    }

    /**
     * Kembalikan ID InternalDepartment berdasarkan role.
     * Null jika role tidak terikat ke department tertentu.
     */
    public function effectiveDepartmentId(): ?int
    {
        return match ($this->role?->nama) {
            'kabag_umum', 'staf_umum'           => 1,
            'kabag_aset', 'staf_aset'           => 2,
            'kabag_pengadaan', 'staf_pengadaan' => 3,
            default                              => null,
        };
    }

    // ── Backward-Compatible Helpers ──────────────────────────────────────────

    /** Checker = admin atau pimpinan (dapat lihat semua, tidak dapat write). */
    public function isChecker(): bool
    {
        return $this->isAdmin() || $this->isPimpinan();
    }

    /** Maker = siapapun selain admin dan pimpinan. */
    public function isMaker(): bool
    {
        return !$this->isChecker();
    }

    // ── Permission Helpers ───────────────────────────────────────────────────

    public function canAccessModule(string $permKey): bool
    {
        // Admin selalu dapat mengakses role_mgmt agar dapat mengatur hak akses via web
        if ($this->isAdmin() && $permKey === 'role_mgmt') {
            return true;
        }

        // Seluruh hak akses role (termasuk admin) disinkronkan langsung via centang di Web Admin (role_permissions)
        return $this->role?->permissions->contains('perm_key', $permKey) ?? false;
    }

    public function canWriteModule(string $permKey): bool
    {
        if ($this->isAdmin() && $permKey === 'role_mgmt') {
            return true;
        }

        $perm = $this->role?->permissions->firstWhere('perm_key', $permKey);
        return (bool) ($perm?->can_write ?? false);
    }
}
