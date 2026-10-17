<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxObjectComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_object_id',
        'type',
        'area_m2',
        'class_code',
        'njop_per_m2',
        'total_njop',
        'tax_year',
    ];

    protected $casts = [
        'area_m2' => 'decimal:2',
        'njop_per_m2' => 'decimal:2',
        'total_njop' => 'decimal:2',
        'tax_year' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (TaxObjectComponent $component) {
            if ($component->area_m2 !== null && $component->njop_per_m2 !== null) {
                if ($component->total_njop === null || ($component->isDirty(['area_m2', 'njop_per_m2']) && ! $component->isDirty('total_njop'))) {
                    $component->total_njop = round((float)$component->area_m2 * (float)$component->njop_per_m2, 2);
                }
            }
        });
    }

    public function taxObject(): BelongsTo
    {
        return $this->belongsTo(TaxObject::class);
    }
}
