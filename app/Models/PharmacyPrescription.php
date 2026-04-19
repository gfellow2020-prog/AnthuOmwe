<?php

namespace App\Models;

use App\Traits\LocksEncounterRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyPrescription extends Model
{
    use LocksEncounterRecords;

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'screening_record_id',
        'prescribed_by',
        'prescription_number',
        'status',
        'notes',
        'prescribed_at',
    ];

    protected $casts = [
        'prescribed_at' => 'datetime',
    ];

    // ─── LocksEncounterRecords contract ───────────────────────────────────────

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function screeningRecord(): BelongsTo
    {
        return $this->belongsTo(ScreeningRecord::class);
    }

    public function prescribedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PharmacyPrescriptionItem::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDispensed(): bool
    {
        return $this->status === 'dispensed';
    }

    public function hasItems(): bool
    {
        return $this->items()->exists();
    }
}
