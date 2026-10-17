<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sppt extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_object_id',
        'tax_year',
        'due_date',
        'njop_dasar',
        'njoptkp',
        'njop_perhitungan',
        'tarif_percent',
        'pbb_terutang',
        'fine_amount',
        'total_bill',
        'paid_amount',
        'status',
        'is_to_blocked',
        'dhkp_row_ref',
    ];

    protected $casts = [
        'tax_year' => 'integer',
        'due_date' => 'date',
        'njop_dasar' => 'decimal:2',
        'njoptkp' => 'decimal:2',
        'njop_perhitungan' => 'decimal:2',
        'tarif_percent' => 'decimal:4',
        'pbb_terutang' => 'decimal:2',
        'fine_amount' => 'decimal:2',
        'total_bill' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'is_to_blocked' => 'boolean',
    ];

    public function taxObject(): BelongsTo
    {
        return $this->belongsTo(TaxObject::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getRemainingBillAttribute(): float
    {
        $remaining = (float)$this->total_bill - (float)$this->paid_amount;
        return max(0, $remaining);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    public function recalculateStatus(): void
    {
        $paid = (float)$this->paid_amount;
        $total = (float)$this->total_bill;

        if ($paid >= $total && $total > 0) {
            $this->status = 'paid';
        } elseif ($paid > 0 && $paid < $total) {
            $this->status = 'partial';
        } else {
            $this->status = 'unpaid';
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'Lunas',
            'partial' => 'Cicilan',
            'unpaid' => 'Belum Bayar',
            default => ucfirst($this->status),
        };
    }
}
