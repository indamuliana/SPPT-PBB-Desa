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
        'kades_name',
        'kades_nip',
        'active_tax_year',
        'target_amount',
        'bank_account_info',
    ];

    protected $casts = [
        'active_tax_year' => 'integer',
        'target_amount' => 'decimal:2',
    ];

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
        ]);
    }
}
