<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncounterAudit extends Model
{
    protected $fillable = [
        'encounter_id',
        'patient_id',
        'action_name',
        'action_stage',
        'action_by',
        'old_values',
        'new_values',
        'notes',
        'action_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'action_at'  => 'datetime',
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

    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
