<?php

namespace App\Models;

use App\Traits\LocksEncounterRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScreeningRecord extends Model
{
    use LocksEncounterRecords;

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'clinician_id',
        'screening_type',
        'complaints',
        'history_of_presenting_illness',
        'past_medical_history',
        'medication_history',
        'allergy_history',
        'physical_examination',
        'clinical_findings',
        'provisional_diagnosis',
        'final_diagnosis',
        'assessment_notes',
        'plan',
        'lab_requested',
        'referred_to_lab_at',
        'returned_from_lab_at',
        'review_notes',
        'prescribed',
        'screening_started_at',
        'screening_completed_at',
    ];

    protected $casts = [
        'lab_requested'         => 'boolean',
        'prescribed'            => 'boolean',
        'referred_to_lab_at'    => 'datetime',
        'returned_from_lab_at'  => 'datetime',
        'screening_started_at'  => 'datetime',
        'screening_completed_at'=> 'datetime',
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

    public function clinician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clinician_id');
    }

    public function staffAssignments(): HasMany
    {
        return $this->hasMany(ScreeningStaffAssignment::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isInitial(): bool
    {
        return $this->screening_type === 'initial';
    }

    public function isReviewAfterLab(): bool
    {
        return $this->screening_type === 'review_after_lab';
    }
}
