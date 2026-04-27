<?php

namespace App\Models;

use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encounter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'encounter_number',
        'patient_id',
        'current_stage',
        'current_status',
        'priority_level',
        'visit_type',
        'started_at',
        'closed_at',
        'started_by',
        'closed_by',
        'closure_notes',
        'is_locked',
    ];

    protected $attributes = [
        'is_locked' => false,
    ];

    protected $casts = [
        'current_stage'  => EncounterStage::class,
        'current_status' => EncounterStatus::class,
        'is_locked'      => 'boolean',
        'started_at'     => 'datetime',
        'closed_at'      => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function stageLogs(): HasMany
    {
        return $this->hasMany(EncounterStageLog::class);
    }

    public function queueTransitions(): HasMany
    {
        return $this->hasMany(EncounterQueueTransition::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(EncounterAudit::class);
    }

    public function registrationRecord(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RegistrationRecord::class);
    }

    public function triageRecord(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TriageRecord::class);
    }

    public function screeningRecord(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ScreeningRecord::class)->where('screening_type', 'initial')->latestOfMany();
    }

    public function screeningReviewRecord(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ScreeningRecord::class)->where('screening_type', 'review_after_lab')->latestOfMany();
    }

    public function labRequest(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(LabRequest::class);
    }

    public function prescription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PharmacyPrescription::class)->latestOfMany();
    }

    public function dispense(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PharmacyDispense::class)->latestOfMany();
    }

    public function startupMedications(): HasMany
    {
        return $this->hasMany(StartupMedication::class);
    }

    // ─── Convenience helpers ──────────────────────────────────────────────────

    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    public function isCompleted(): bool
    {
        return $this->current_stage === EncounterStage::Completed;
    }

    public function isAtStage(EncounterStage $stage): bool
    {
        return $this->current_stage === $stage;
    }

    public function hasStatus(EncounterStatus $status): bool
    {
        return $this->current_status === $status;
    }

    /**
     * Returns the open (pending or received) queue transition for the current stage.
     */
    public function activeQueueTransition(): ?EncounterQueueTransition
    {
        return $this->queueTransitions()
            ->where('to_stage', $this->current_stage->value)
            ->whereIn('status', ['queued', 'received'])
            ->latest()
            ->first();
    }

    /**
     * Returns the active stage log for the current stage.
     */
    public function activeStageLog(): ?EncounterStageLog
    {
        return $this->stageLogs()
            ->where('stage_name', $this->current_stage->value)
            ->whereNull('completed_at')
            ->latest()
            ->first();
    }
}
