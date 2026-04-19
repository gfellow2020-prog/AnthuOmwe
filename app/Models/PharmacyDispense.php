<?php

namespace App\Models;

use App\Traits\LocksEncounterRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyDispense extends Model
{
    use LocksEncounterRecords;

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'pharmacy_prescription_id',
        'dispensed_by',
        'dispensing_notes',
        'counseling_notes',
        'dispensed_at',
    ];

    protected $casts = [
        'dispensed_at' => 'datetime',
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

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(PharmacyPrescription::class, 'pharmacy_prescription_id');
    }

    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PharmacyDispenseItem::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function hasItems(): bool
    {
        return $this->items()->exists();
    }
}
