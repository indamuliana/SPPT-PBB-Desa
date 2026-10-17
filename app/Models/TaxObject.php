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

    public function toCases(): HasMany
    {
        return $this->hasMany(ToCase::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(SpptMutation::class);
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

    public static function generateChildNop(string $baseNop): string
    {
        $clean = preg_replace('/[^0-9]/', '', $baseNop);
        $prefix = strlen($clean) >= 14 ? substr($clean, 0, 14) : str_pad($clean, 14, '0');
        $suffix = strlen($clean) > 14 ? (int)substr($clean, 14) : 1000;

        do {
            $suffix++;
            $candidate = $prefix . str_pad((string)($suffix % 10000), 4, '0', STR_PAD_LEFT);
        } while (static::where('nop', $candidate)->exists());

        return $candidate;
    }
}
