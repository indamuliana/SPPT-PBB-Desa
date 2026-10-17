<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TaxObject extends Model
{
    use HasFactory;

    protected $fillable = [
        'nop',
        'taxpayer_id',
        'address',
        'rt',
        'rw',
        'dusun',
        'status',
        'notes',
    ];

    public function taxpayer(): BelongsTo
    {
        return $this->belongsTo(Taxpayer::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(TaxObjectComponent::class);
    }

    public function sppts(): HasMany
    {
        return $this->hasMany(Sppt::class);
    }

    public function spptForYear(int $year): HasOne
    {
        return $this->hasOne(Sppt::class)->where('tax_year', $year);
    }

    public function isTo(): bool
    {
        return $this->status === 'to';
    }

    public function getFormattedNopAttribute(): string
    {
        $raw = preg_replace('/[^0-9]/', '', (string)$this->nop);
        if (strlen($raw) === 18) {
            // Standard Indonesian NOP format: XX.XX.XXX.XXX.XXX-XXXX.X
            return substr($raw, 0, 2) . '.' .
                   substr($raw, 2, 2) . '.' .
                   substr($raw, 4, 3) . '.' .
                   substr($raw, 7, 3) . '.' .
                   substr($raw, 10, 3) . '-' .
                   substr($raw, 13, 4) . '.' .
                   substr($raw, 17, 1);
        }
        return (string)$this->nop;
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->rt ? 'RT ' . $this->rt : null,
            $this->rw ? 'RW ' . $this->rw : null,
            $this->dusun,
        ]);

        return ! empty($parts) ? implode(', ', $parts) : '-';
    }
}
