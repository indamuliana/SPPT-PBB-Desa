<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_number',
        'tax_object_id',
        'sppt_id',
        'reported_by_user_id',
        'assigned_to_user_id',
        'reason_type',
        'description',
        'investigation_notes',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public static function generateCaseNumber(): string
    {
        $prefix = 'TO-' . date('Ymd') . '-';
        $latest = static::where('case_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('case_number');

        $seq = $latest ? ((int)substr($latest, -4)) + 1 : 1;
        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    public function taxObject(): BelongsTo
    {
        return $this->belongsTo(TaxObject::class);
    }

    public function sppt(): BelongsTo
    {
        return $this->belongsTo(Sppt::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(SpptMutation::class, 'to_case_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isInvestigating(): bool
    {
        return $this->status === 'investigating';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason_type) {
            'pemilik_tidak_dikenal' => 'Subjek Pajak Tidak Dikenal / Ditemukan',
            'sengketa' => 'Sengketa Kepemilikan Lahan',
            'objek_tidak_ada' => 'Fisik Objek Tidak Ditemukan',
            'pindah_tanpa_kabar' => 'Pemilik Pindah Tanpa Alamat Jelas',
            default => ucwords(str_replace('_', ' ', $this->reason_type)),
        };
    }
}
