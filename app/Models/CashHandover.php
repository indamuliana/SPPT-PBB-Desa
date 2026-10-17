<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashHandover extends Model
{
    use HasFactory;

    protected $fillable = [
        'handover_number',
        'cashier_user_id',
        'collector_user_id',
        'handover_date',
        'system_amount',
        'physical_amount',
        'difference_amount',
        'difference_reason',
        'status',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'handover_date' => 'datetime',
        'received_at' => 'datetime',
        'system_amount' => 'decimal:2',
        'physical_amount' => 'decimal:2',
        'difference_amount' => 'decimal:2',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_user_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'handover_id');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public static function generateHandoverNumber(): string
    {
        $dateStr = now()->format('Ymd');
        $prefix = "STK-{$dateStr}-";

        $last = static::where('handover_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('handover_number');

        if ($last) {
            $seq = (int)substr($last, strlen($prefix)) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
}
