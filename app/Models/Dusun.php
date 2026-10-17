<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Dusun extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'rw',
        'description',
    ];

    /**
     * Kadus penanggung jawab wilayah dusun ini.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'dusun_user')->withTimestamps();
    }

    public function kadus(): BelongsToMany
    {
        return $this->users();
    }

    /**
     * Objek pajak yang berada di dusun ini.
     */
    public function taxObjects(): HasMany
    {
        return $this->hasMany(TaxObject::class);
    }

    /**
     * SPPT yang terbit untuk objek pajak di dusun ini.
     */
    public function sppts(): HasManyThrough
    {
        return $this->hasManyThrough(Sppt::class, TaxObject::class);
    }

    /**
     * Nama-nama kadus penanggung jawab yang diformat koma.
     */
    public function getKadusNamesAttribute(): string
    {
        $names = $this->users->pluck('name')->toArray();
        return ! empty($names) ? implode(', ', $names) : 'Belum Ditugaskan';
    }
}
