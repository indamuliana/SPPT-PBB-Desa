<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'verification_token',
        'sppt_id',
        'cashier_user_id',
        'payment_date',
        'amount_principal',
        'amount_fine',
        'total_amount',
        'remaining_after',
        'payment_method',
        'handover_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount_principal' => 'decimal:2',
        'amount_fine' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'remaining_after' => 'decimal:2',
    ];

    public function sppt(): BelongsTo
    {
        return $this->belongsTo(Sppt::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_user_id');
    }

    public function cancellation(): HasOne
    {
        return $this->hasOne(PaymentCancellation::class)->latestOfMany();
    }

    public function handover(): BelongsTo
    {
        return $this->belongsTo(CashHandover::class, 'handover_id');
    }

    public function isValid(): bool
    {
        return $this->status === 'valid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isCancellationRequested(): bool
    {
        return $this->status === 'cancellation_requested';
    }

    public function getVerificationUrlAttribute(): string
    {
        return url('/verifikasi/' . $this->verification_token);
    }

    /**
     * Generate unique sequential receipt number: PBB-YYYYMMDD-XXXXX
     */
    public static function generateReceiptNumber(): string
    {
        $dateStr = now()->format('Ymd');
        $prefix = "PBB-{$dateStr}-";

        $last = static::where('receipt_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('receipt_number');

        if ($last) {
            $seq = (int)substr($last, strlen($prefix)) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad((string)$seq, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate secure 32-byte cryptographically random token
     */
    public static function generateVerificationToken(): string
    {
        return bin2hex(random_bytes(16));
    }
}
