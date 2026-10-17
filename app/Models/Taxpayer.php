<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Taxpayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'name',
        'phone',
        'address',
        'rt',
        'rw',
        'dusun',
        'village_name',
        'district_name',
        'regency_name',
        'status',
        'notes',
    ];

    public function taxObjects(): HasMany
    {
        return $this->hasMany(TaxObject::class);
    }

    public function sppts(): HasManyThrough
    {
        return $this->hasManyThrough(Sppt::class, TaxObject::class);
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->rt ? 'RT ' . $this->rt : null,
            $this->rw ? 'RW ' . $this->rw : null,
            $this->dusun,
            $this->village_name,
        ]);

        return ! empty($parts) ? implode(', ', $parts) : '-';
    }
}
