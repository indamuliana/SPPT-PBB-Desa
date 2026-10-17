<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $fillable = [
        'name',
        'district',
        'regency',
        'province',
        'postal_code',
        'address',
        'phone',
        'email',
        'logo_path',
        'regency_logo_path',
        'hero_image_path',
        'kades_name',
        'kades_nip',
        'active_tax_year',
        'target_amount',
        'tax_rate_percent',
        'bank_account_info',
    ];

    protected $casts = [
        'active_tax_year' => 'integer',
        'target_amount' => 'decimal:2',
        'tax_rate_percent' => 'decimal:3',
    ];

    /**
     * Get URL for logo desa
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path && file_exists(public_path($this->logo_path))) {
            return asset($this->logo_path);
        }
        return null;
    }

    /**
     * Get URL for logo kabupaten
     */
    public function getRegencyLogoUrlAttribute(): ?string
    {
        if ($this->regency_logo_path && file_exists(public_path($this->regency_logo_path))) {
            return asset($this->regency_logo_path);
        }
        return null;
    }

    /**
     * Get URL for hero welcome photo
     */
    public function getHeroImageUrlAttribute(): string
    {
        if ($this->hero_image_path && file_exists(public_path($this->hero_image_path))) {
            return asset($this->hero_image_path);
        }
        return asset('images/kantor-desa-sukahaji.png');
    }

    /**
     * Helper to get the village profile (single row)
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'name' => 'Desa Sukahaji',
            'district' => 'Kecamatan Patrol',
            'regency' => 'Kabupaten Indramayu',
            'province' => 'Jawa Barat',
            'active_tax_year' => 2026,
            'target_amount' => 150000000,
            'tax_rate_percent' => 0.100,
        ]);
    }
}
