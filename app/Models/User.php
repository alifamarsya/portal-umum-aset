<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

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
        'last_login'
    ];

    protected function casts(): array
    {
        return [
        'is_active' => 'boolean',
        'must_change_pwd' => 'boolean',
        'last_login' => 'datetime'
        ];
    }
    use Notifiable;

    protected $hidden = ['password', 'rememberToken'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isSuperadmin(): bool
    {
        return $this->role?->nama === 'superadmin';
    }

    public function isPimpinan(): bool
    {
        return $this->role?->nama === 'pimpinan';
    }

    public function isChecker(): bool
    {
        return $this->isPimpinan();
    }

    public function isMaker(): bool
    {
        return !in_array($this->role?->nama, ['superadmin', 'pimpinan']);
    }

    public function canAccessModule(string $permKey): bool
    {
        if (in_array($this->role?->nama, ['superadmin', 'pimpinan'])) {
            return true;
        }

        return $this->role?->permissions->contains('perm_key', $permKey) ?? false;
    }

    public function canWriteModule(string $permKey): bool
    {
        if (in_array($this->role?->nama, ['superadmin', 'pimpinan'])) {
            return false;
        }

        $perm = $this->role?->permissions->firstWhere('perm_key', $permKey);
        return (bool) ($perm?->can_write ?? false);
    }
}
