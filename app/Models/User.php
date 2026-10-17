<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'dusun_name',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function dusuns(): BelongsToMany
    {
        return $this->belongsToMany(Dusun::class, 'dusun_user')->withTimestamps();
    }

    public function assignedDusunIds(): array
    {
        return $this->dusuns->pluck('id')->toArray();
    }

    public function assignedDusunNames(): string
    {
        $names = $this->dusuns->pluck('name')->toArray();
        if (! empty($names)) {
            return implode(', ', $names);
        }
        return $this->dusun_name ?: 'Belum Ada Penugasan';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['kolektor', 'admin'], true);
    }

    public function isKadus(): bool
    {
        return $this->role === 'kadus';
    }

    public function isKolektor(): bool
    {
        return $this->role === 'kolektor';
    }

    public function isBendahara(): bool
    {
        return in_array($this->role, ['kolektor', 'bendahara'], true);
    }

    public function isKades(): bool
    {
        return $this->role === 'kades';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'kolektor' => 'Kolektor Desa',
            'kadus' => 'Kadus / Kasir',
            'kades' => 'Kepala Desa',
            'admin' => 'Kolektor Desa',
            'bendahara' => 'Kolektor Desa',
            default => ucfirst($this->role),
        };
    }
}
