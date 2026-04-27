<?php

namespace App\Models;

use App\Traits\LocksEncounterRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TriageRecord extends Model
{
    use LocksEncounterRecords;

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'nurse_id',
        'weight',
        'height',
        'bmi',
        'temperature',
        'pulse',
        'respiratory_rate',
        'systolic_bp',
        'diastolic_bp',
        'oxygen_saturation',
        'blood_sugar',
        'pain_scale',
        'muac',
        'muac_score',
        'abdominal_circumference',
        'chief_complaint_brief',
        'startup_interventions_notes',
        'startup_medications_notes',
        'triage_notes',
        'triage_at',
        'completed_at',
    ];

    protected $casts = [
        'weight'             => 'decimal:2',
        'height'             => 'decimal:2',
        'bmi'                => 'decimal:2',
        'temperature'        => 'decimal:1',
        'oxygen_saturation'  => 'decimal:1',
        'blood_sugar'        => 'decimal:2',
        'muac'               => 'decimal:1',
        'abdominal_circumference' => 'decimal:1',
        'triage_at'          => 'datetime',
        'completed_at'       => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Automatically compute BMI from weight (kg) and height (cm).
     */
    public static function computeBmi(?float $weight, ?float $height): ?float
    {
        if (! $weight || ! $height || $height <= 0) {
            return null;
        }
        $heightM = $height / 100;
        return round($weight / ($heightM * $heightM), 2);
    }

    public function bloodPressure(): string
    {
        if ($this->systolic_bp && $this->diastolic_bp) {
            return "{$this->systolic_bp}/{$this->diastolic_bp} mmHg";
        }
        return '—';
    }

    public function startupMedications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StartupMedication::class);
    }
}
