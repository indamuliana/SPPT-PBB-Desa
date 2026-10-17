<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
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
        return $this->role === 'bendahara';
    }

    public function isKades(): bool
    {
        return $this->role === 'kades';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Admin Desa',
            'kadus' => 'Kadus / Kasir',
            'kolektor' => 'Kolektor Desa',
            'bendahara' => 'Bendahara Desa',
            'kades' => 'Kepala Desa',
            default => ucfirst($this->role),
        };
    }
}
