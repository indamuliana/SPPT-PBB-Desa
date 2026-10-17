<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_number',
        'collector_user_id',
        'verifier_user_id',
        'deposit_date',
        'destination_bank',
        'deposit_amount',
        'bank_ref_number',
        'proof_file_path',
        'status',
        'rejection_notes',
        'approved_at',
    ];

    protected $casts = [
        'deposit_date' => 'date',
        'deposit_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_user_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_user_id');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public static function generateDepositNumber(): string
    {
        $dateStr = now()->format('Ymd');
        $prefix = "SET-{$dateStr}-";

        $last = static::where('deposit_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('deposit_number');

        if ($last) {
            $seq = (int)substr($last, strlen($prefix)) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
}
