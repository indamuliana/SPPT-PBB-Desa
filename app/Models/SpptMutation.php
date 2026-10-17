<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpptMutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'mutation_number',
        'tax_object_id',
        'to_case_id',
        'mutation_type',
        'old_taxpayer_id',
        'new_taxpayer_id',
        'requested_by_user_id',
        'approved_by_user_id',
        'legal_doc_type',
        'legal_doc_number',
        'reason',
        'status',
        'rejection_notes',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public static function generateMutationNumber(): string
    {
        $prefix = 'MUT-' . date('Ymd') . '-';
        $latest = static::where('mutation_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('mutation_number');

        $seq = $latest ? ((int)substr($latest, -4)) + 1 : 1;
        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    public function taxObject(): BelongsTo
    {
        return $this->belongsTo(TaxObject::class);
    }

    public function toCase(): BelongsTo
    {
        return $this->belongsTo(ToCase::class);
    }

    public function oldTaxpayer(): BelongsTo
    {
        return $this->belongsTo(Taxpayer::class, 'old_taxpayer_id');
    }

    public function newTaxpayer(): BelongsTo
    {
        return $this->belongsTo(Taxpayer::class, 'new_taxpayer_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->mutation_type) {
            'balik_nama' => 'Balik Nama Kepemilikan',
            'pemecahan' => 'Pemecahan Objek Pajak',
            'penggabungan' => 'Penggabungan Objek Pajak',
            'koreksi_data' => 'Koreksi Data / Luas',
            default => ucwords(str_replace('_', ' ', $this->mutation_type)),
        };
    }
}
