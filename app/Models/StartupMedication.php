<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StartupMedication extends Model
{
    protected $fillable = [
        'encounter_id',
        'triage_record_id',
        'patient_id',
        'recorded_by',
        'medication_id',
        'medication_name',
        'dosage',
        'route',
        'frequency',
        'notes',
        'administered_at',
    ];

    protected $casts = [
        'administered_at' => 'datetime',
    ];

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function triageRecord(): BelongsTo
    {
        return $this->belongsTo(TriageRecord::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
